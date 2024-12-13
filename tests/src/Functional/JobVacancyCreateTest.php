<?php

namespace Drupal\Tests\localgov_job_vacancies\Functional;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\file\Entity\File;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\TestFileCreationTrait;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test that users with the correct permissions can create job vacancies.
 *
 * @group localgov_job_vacancies
 */
class JobVacancyCreateTest extends BrowserTestBase {

  use TestFileCreationTrait {
    getTestFiles as drupalGetTestFiles;
  }

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
  protected $profile = 'localgov';

  /**
   * Module dependencies that need to be installed.
   *
   * @var string[]
   *   An array of module machine names.
   */
  protected static $modules = [
    'localgov_workflows',
    'localgov_job_vacancies',
    'node',
    'content_moderation',
    'datetime',
    'file',
  ];

  /**
   * Admin user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * A document file path for uploading.
   *
   * @var \Drupal\file\FileInterface
   */
  protected $document;

  /**
   * Test setup function.
   *
   * @return void
   *   Don't need to return anything.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function setUp(): void {
    parent::setUp();

    // Create admin user.
    $this->adminUser = $this->drupalCreateUser([
      'access administration pages',
      'administer nodes',
      'access content overview',
      'create localgov_job_vacancy content',
      'use localgov_editorial transition publish',
    ]);

    $this->drupalPlaceBlock('local_tasks_block');
  }

  /**
   * Test that we can create a job vacancy.
   *
   * @return void
   *   Return nothing.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function testJobVacancyCreate() {
    $document_files = $this->drupalGetTestFiles('text');
    $this->document = File::create((array) current($document_files));
    $this->assertFileExists($this->document->getFileUri());

    $get_page = $this->getSession()->getPage();
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/content');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);
    $this->assertSession()->pageTextContains('Add content');
    $get_page->clickLink('Add content');
    $this->assertSession()->addressEquals('node/add/localgov_job_vacancy');
    $this->assertSession()->pageTextContains('Create Job Vacancy');

    $closing_date = DrupalDateTime::createFromTimestamp(time());
    $closing_date->setTimezone(new \DateTimeZone('UTC'));
    $closing_date->modify('+2 months');

    $get_page->fillField('Title', 'Test vacancy');
    $get_page->fillField('Body', 'The quick brown fox jumps over the lazy dog.');
    $get_page->fillField('localgov_closing_date[0][value][date]', $closing_date->format('Y-m-d'));
    $get_page->fillField('localgov_closing_date[0][value][time]', $closing_date->format('H:i:s'));
    $get_page->fillField('files[localgov_application_forms_0][]', $this->document->id());
    $get_page->fillField('moderation_state[0][state]', 'published');
    $get_page->pressButton('Save');

    $this->assertSession()->addressEquals('/job-vacancies/test-vacancy');
    $this->assertSession()->pageTextContains('Job Vacancy Test vacancy has been created.');
    $this->assertSession()->pageTextContains('Test vacancy');
    $this->assertSession()->pageTextContains('The quick brown fox jumps over the lazy dog.');
    $this->assertSession()->pageTextContains($closing_date->format('D d/m/Y g:ia'));
  }

}
