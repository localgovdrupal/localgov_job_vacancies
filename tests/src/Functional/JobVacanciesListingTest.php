<?php

namespace Drupal\Tests\localgov_job_vacancies\Functional;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\Traits\Core\CronRunTrait;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test that the job vacancies listings are correct.
 */
class JobVacanciesListingTest extends BrowserTestBase {

  use TestFileCreationTrait {
    getTestFiles as drupalGetTestFiles;
  }
  use CronRunTrait;

  /**
   * Default theme to use.
   *
   * @var string
   */
  protected $defaultTheme = 'localgov_scarfolk';

  /**
   * Install profile to use.
   *
   * @var string
   */
  protected $profile = 'testing';

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Module dependencies that need to be installed.
   *
   * @var string[]
   *   An array of module machine names.
   */
  protected static $modules = [
    'block',
    'datetime',
    'field_ui',
    'localgov_search',
    'localgov_job_vacancies',
    'menu_ui',
    'search_api',
    'pathauto',
    'node',
    'views',
  ];

  /**
   * Test setup function.
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalPlaceBlock('localgov_job_vacancies_search_job_vacancies_page', [
      'region' => 'sidebar_first',
    ]);
    $this->fileSystem = \Drupal::service('file_system');
  }

  /**
   * Test the job vacancies listing page is accessible.
   *
   * @return void
   *   Return nothing.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function testJobVacanciesListingPage() {
    $this->drupalGet('/job-vacancies');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);
  }

  /**
   * Test that the posts we expected are showing.
   */
  public function testJobVacanciesAreVisible(): void {
    // Arrange.
    $closing_date = DrupalDateTime::createFromTimestamp(time());
    $closing_date->setTimezone(new \DateTimeZone('UTC'));
    $closing_date->modify('+2 weeks');
    $document = current($this->drupalGetTestFiles('text'));
    $this->createNode([
      'type' => 'localgov_job_vacancy',
      'title' => 'First post',
      'body' => 'The quick brown fox jumps over the lazy dog.',
      'localgov_closing_date[0][value][date]' => $closing_date->format('Y-m-d'),
      'localgov_closing_date[0][value][time]' => $closing_date->format('H:i:s'),
      'moderation_state' => 'published',
      'files[localgov_application_forms_0][]' => $this->fileSystem->realpath($document->uri),

    ]);

    $closing_date_job2 = DrupalDateTime::createFromTimestamp(time());
    $closing_date_job2->setTimezone(new \DateTimeZone('UTC'));
    $closing_date_job2->modify('+1 month');

    $this->createNode(values: [
      'type' => 'localgov_job_vacancy',
      'title' => 'Second post',
      'body' => 'The quick brown fox jumps over the lazy dog.',
      'localgov_closing_date[0][value][date]' => $closing_date_job2->format('Y-m-d'),
      'localgov_closing_date[0][value][time]' => $closing_date_job2->format('H:i:s'),
      'moderation_state' => 'published',
      'files[localgov_application_forms_0][]' => $this->fileSystem->realpath($document->uri),
    ]);

    $closing_date_job3 = DrupalDateTime::createFromTimestamp(time());
    $closing_date_job3->setTimezone(new \DateTimeZone('UTC'));
    $closing_date_job3->modify('+2 months');

    $this->createNode([
      'type' => 'localgov_job_vacancy',
      'title' => 'Third post',
      'body' => 'The quick brown fox jumps over the lazy dog.',
      'localgov_closing_date[0][value][date]' => $closing_date_job3->format('Y-m-d'),
      'localgov_closing_date[0][value][time]' => $closing_date_job3->format('H:i:s'),
      'moderation_state' => 'published',
      'files[localgov_application_forms_0][]' => $this->fileSystem->realpath($document->uri),
    ]);

    // Act.
    $this->cronRun();
    $this->drupalGet('/job-vacancies');

    // Assert.
    $assert = $this->assertSession();
    $assert->statusCodeEquals(Response::HTTP_OK);
    $assert->pageTextContains('First post');
    $assert->pageTextContains('Second post');
    $assert->pageTextContains('Third post');
  }

  /**
   * Test that the posts we expected are showing.
   */
  public function testUnpublishdJobVacanciesAreNotVisible(): void {
    // Arrange.
    $closing_date = DrupalDateTime::createFromTimestamp(time());
    $closing_date->setTimezone(new \DateTimeZone('UTC'));
    $closing_date->modify('+2 weeks');
    $document = current($this->drupalGetTestFiles('text'));
    $this->createNode([
      'type' => 'localgov_job_vacancy',
      'title' => 'First post',
      'body' => 'The quick brown fox jumps over the lazy dog.',
      'localgov_closing_date[0][value][date]' => $closing_date->format('Y-m-d'),
      'localgov_closing_date[0][value][time]' => $closing_date->format('H:i:s'),
      'moderation_state' => 'published',
      'files[localgov_application_forms_0][]' => $this->fileSystem->realpath($document->uri),

    ]);

    $closing_date_job2 = DrupalDateTime::createFromTimestamp(time());
    $closing_date_job2->setTimezone(new \DateTimeZone('UTC'));
    $closing_date_job2->modify('+1 month');

    $this->createNode(values: [
      'type' => 'localgov_job_vacancy',
      'title' => 'Second post',
      'body' => 'The quick brown fox jumps over the lazy dog.',
      'localgov_closing_date[0][value][date]' => $closing_date_job2->format('Y-m-d'),
      'localgov_closing_date[0][value][time]' => $closing_date_job2->format('H:i:s'),
      'files[localgov_application_forms_0][]' => $this->fileSystem->realpath($document->uri),
    ]);

    $closing_date_job3 = DrupalDateTime::createFromTimestamp(time());
    $closing_date_job3->setTimezone(new \DateTimeZone('UTC'));
    $closing_date_job3->modify('+2 months');

    $this->createNode([
      'type' => 'localgov_job_vacancy',
      'title' => 'Third post',
      'body' => 'The quick brown fox jumps over the lazy dog.',
      'localgov_closing_date[0][value][date]' => $closing_date_job3->format('Y-m-d'),
      'localgov_closing_date[0][value][time]' => $closing_date_job3->format('H:i:s'),
      'moderation_state' => 'published',
      'files[localgov_application_forms_0][]' => $this->fileSystem->realpath($document->uri),
    ]);

    // Act.
    $this->cronRun();
    $this->drupalGet('/job-vacancies');

    // Assert.
    $assert = $this->assertSession();
    $assert->statusCodeEquals(Response::HTTP_OK);
    $assert->pageTextContains('First post');
    $assert->pageTextNotContains('Second post');
    $assert->pageTextContains('Third post');
  }

}
