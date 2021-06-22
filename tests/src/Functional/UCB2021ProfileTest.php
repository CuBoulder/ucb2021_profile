<?php

namespace Drupal\Tests\ucb2021_profile\Functional;

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\InstallStorage;
use Drupal\Core\Config\StorageInterface;
use Drupal\KernelTests\AssertConfigTrait;
use Drupal\Tests\BrowserTestBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Component\Render\FormattableMarkup;

/**
 * Tests ucb2021_profile profile.
 *
 * @group ucb2021_profile
 */
class UCB2021ProfileTest extends BrowserTestBase {
  use AssertConfigTrait;

  /**
   * {@inheritdoc}
   */
  protected function installParameters() {
    $parameters = parent::installParameters();
    $parameters['forms']['install_configure_form']['site_mail'] = 'admin@example.com';
    return $parameters;
  }

  /**
   * {@inheritdoc}
   */
  protected $profile = 'ucb2021_profile';

  /**
   * Tests the profile supplied configuration is the same after installation.
   */
  public function testConfig() {
    // Just connect directly to the config table so we don't need to worry about the cache layer.
    $active_config_storage = $this->container->get('config.storage');

    $default_config_storage = new FileStorage(drupal_get_path('profile', $this->profile) . '/' . InstallStorage::CONFIG_INSTALL_DIRECTORY, InstallStorage::DEFAULT_COLLECTION);
    $this->assertDefaultConfig($default_config_storage, $active_config_storage);

    $default_config_storage = new FileStorage(drupal_get_path('profile', $this->profile) . '/' . InstallStorage::CONFIG_OPTIONAL_DIRECTORY, InstallStorage::DEFAULT_COLLECTION);
    $this->assertDefaultConfig($default_config_storage, $active_config_storage);
  }

  /**
   * Asserts that the default configuration matches active configuration.
   *
   * @param \Drupal\Core\Config\StorageInterface $default_config_storage
   *   The default configuration storage to check.
   * @param \Drupal\Core\Config\StorageInterface $active_config_storage
   *   The active configuration storage.
   */
  protected function assertDefaultConfig(StorageInterface $default_config_storage, StorageInterface $active_config_storage) {
    /** @var \Drupal\Core\Config\ConfigManagerInterface $config_manager */
    $config_manager = $this->container->get('config.manager');

    foreach ($default_config_storage->listAll() as $config_name) {
      if ($active_config_storage->exists($config_name)) {
        $result = $config_manager->diff($default_config_storage, $active_config_storage, $config_name);
        $this->assertConfigDiff($result, $config_name, [
          // The filter.format.*:roles key is a special install key.
          'filter.format.basic_html' => ['roles:', '  - authenticated'],
          'filter.format.full_html' => ['roles:', '  - administrator'],
          'filter.format.restricted_html' => ['roles:', '  - anonymous'],
          // The system.site config is overwritten during tests by
          // FunctionalTestSetupTrait::installParameters().
          'system.site' => ['uuid:', 'name:', 'mail:'],
        ]);
      }
      else {
        $this->fail("$config_name has not been installed");
      }
    }
  }

  /**
   * Tests that the users can log in with the admin password selected at install.
   */
  public function testUser() {
    $password = $this->rootUser->pass_raw;
    $ids = \Drupal::entityQuery('user')
      ->accessCheck(FALSE)
      ->condition('roles', ['author', 'editor'], 'IN')
      ->execute();

    $users = \Drupal::entityTypeManager()->getStorage('user')->loadMultiple($ids);

    foreach ($users as $user) {
      $this->drupalLoginWithPassword($user, $password);
    }
  }

  /**
   * Tests that the CU base theme is available on the Appearance page.
   */
  public function testAppearance() {
    $account = $this->drupalCreateUser(['administer themes']);
    $this->drupalLogin($account);
    $webassert = $this->assertSession();

    $this->drupalGet('admin/appearance');
    $webassert->pageTextContains('CU Boulder 2021 Base Theme');
  }

  /**
   * Logs in a user using the Mink controlled browser using a password.
   *
   * If a user is already logged in, then the current user is logged out before
   * logging in the specified user.
   *
   * Please note that neither the current user nor the passed-in user object is
   * populated with data of the logged in user. If you need full access to the
   * user object after logging in, it must be updated manually. If you also need
   * access to the plain-text password of the user (set by drupalCreateUser()),
   * e.g. to log in the same user again, then it must be re-assigned manually.
   * For example:
   * @code
   *   // Create a user.
   *   $account = $this->drupalCreateUser(array());
   *   $this->drupalLogin($account);
   *   // Load real user object.
   *   $pass_raw = $account->passRaw;
   *   $account = User::load($account->id());
   *   $account->passRaw = $pass_raw;
   * @endcode
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   User object representing the user to log in.
   * @param string $password
   *   The password to authenticate the user with.
   *
   * @see drupalCreateUser()
   */
  protected function drupalLoginWithPassword(AccountInterface $account, $password) {
    if ($this->loggedInUser) {
      $this->drupalLogout();
    }

    $this->drupalGet('user/login');
    $this->submitForm([
      'name' => $account->getAccountName(),
      'pass' => $password,
    ], t('Log in'));

    // @see ::drupalUserIsLoggedIn()
    $account->sessionId = $this->getSession()->getCookie(\Drupal::service('session_configuration')->getOptions(\Drupal::request())['name']);
    $this->assertTrue($this->drupalUserIsLoggedIn($account), new FormattableMarkup('User %name successfully logged in.', ['%name' => $account->getAccountName()]));

    $this->loggedInUser = $account;
    $this->container->get('current_user')->setAccount($account);
  }

}
