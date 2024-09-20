<?php

namespace Drupal\Tests\localgov_job_vacancies\Kernel;

use Drupal\Core\Url;
use Drupal\KernelTests\KernelTestBase;
use Drupal\views\Views;

/**
 * Test job vacancies view url.
 *
 * @group localgov_job_vacancies
 */
class JobVacanciesViewUrlTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'block',
    'localgov_job_vacancies',
    'datetime',
    'field',
    'file',
    'filefield_paths',
    'field_formatter_class',
    'language',
    'menu_ui',
    'node',
    'path',
    'path_alias',
    'pathauto',
    'search_api',
    'search_api_autocomplete',
    'search_api_db',
    'system',
    'text',
    'token',
    'user',
    'views',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('file');
    $this->installEntitySchema('node');
    $this->installEntitySchema('search_api_task');
    $this->installEntitySchema('user');
    $this->installEntitySchema('view');
    $this->installEntitySchema('field_storage_config');
    $this->installEntitySchema('field_config');
    $this->installEntitySchema('path_alias');
    $this->installSchema('file', ['file_usage']);
    $this->installSchema('node', ['node_access']);
    $this->installSchema('user', ['users_data']);
    $this->installSchema('search_api', ['search_api_item']);
    $this->installConfig([
      'block',
      'datetime',
      'field',
      'filefield_paths',
      'field_formatter_class',
      'menu_ui',
      'pathauto',
      'node',
      'search_api',
      'search_api_db',
      'system',
      'text',
      'user',
      'views',
      'localgov_job_vacancies',
    ]);
  }

  /**
   * Test job vacancies view url.
   *
   * @throws \Exception
   */
  public function testJobVacanciesViewUrl() :void {
    $this->assertTrue(TRUE);

    $view = Views::getView('localgov_job_vacancies_search');
    $view->setDisplay('job_vacancies_page');

    $view_url_route = $view->getUrl()->getRouteName();
    $url = Url::fromRoute($view_url_route);

    $this->assertEquals('/job-vacancies', $url->toString());
  }

}
