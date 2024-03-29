<?php

namespace controller;

require_once("view/LoginView.php");
require_once("model/UserDatabase.php");
require_once("model/FlashModel.php");
require_once("model/SessionModel.php");

class LoginController {
  private static $username = '';
  private static $password = '';

  public function __construct($flashModel, $sessionModel) {
    $this->lw = new \view\LoginView();
    $this->sm = $sessionModel;
    $this->fm = $flashModel;

    try {

      if (isset($_COOKIE['Username']) && !$this->lw->isLoggingOut()) {
        self::$username = $_COOKIE['Username'];
        self::$password = $_COOKIE['Password'];
        $this->compareEnteredCredentials();
      } else {
        if ($this->lw->isLoggingIn() && !$this->sm->getIsLoggedIn()) {
          $this->setUsername();
          $this->setPassword();
          $this->compareEnteredCredentials();
          $this->storeUserCredentialsInCookie();
        } else if ($this->lw->isLoggingOut() && $this->sm->getIsLoggedIn()) {
          $this->removeUserCredentialsInCookie();
          $_SESSION['username'] = '';
          $_SESSION['isLoggedIn'] = false;
          $_SESSION['message'] = 'Bye bye!';
        }
      }

      } catch (\Exception $e) {
        $_SESSION['username'] = self::$username;
        $_SESSION['message'] = $e->getMessage();
      } finally {
        $this->lw->loginToLayoutView($this->fm, $this->sm);
        if ($this->compareEnteredCredentials() || $this->lw->isLoggingOut()) {
          header('Location: /');
          exit();
        }
      }
  }

  private function getExistingUsers() {
    $udb = new \model\UserDatabase();
    return $udb->superRealDatabase();
  }

  private function compareEnteredCredentials() {

    $existingUsers = $this->getExistingUsers();
    $testing = false;

    if ($existingUsers['username'] == self::$username && $existingUsers['password'] == self::$password) {
      $_SESSION['isLoggedIn'] = true;
      if ($this->lw->isKeepingLogin()) {
        $_SESSION['message'] = 'Welcome and you will be remembered';
        $testing = true;
      } else if (!$this->sm->getIsLoggedIn() && isset($_COOKIE['Username'])) {
        $_SESSION['message'] = 'Welcome back with cookie';
        $testing = true;
      } else if (!$this->sm->getIsLoggedIn()) {
        $_SESSION['message'] = 'Welcome';
        $testing = true;
      }
    } else if (strlen(self::$password) > 0 || strlen(self::$username) > 0) {
      $_SESSION['message'] = 'Wrong name or password';
    }

    return $testing;

  }

  private function setUsername() {
    if (isset($_COOKIE['Username'])) {
      self::$username = $_COOKIE['Username'];
    } else {
      self::$username = $this->lw->getUsername();
    }
  }

  private function setPassword() {
    if (isset($_COOKIE['Password'])) {
      self::$password = $_COOKIE['Password'];
    } else {
      self::$password = $this->lw->getPassword();
    }
  }

  private function storeUserCredentialsInCookie() {
    if ($this->lw->isKeepingLogin()) {
      $this->setUsernameCookie();
      $this->setPasswordCookie();
    }
  }

  private function removeUserCredentialsInCookie() {
    $this->removeUsernameCookie();
    $this->removePasswordCookie();
  }

  private function setUsernameCookie() {
    setcookie("Username", self::$username, time()+36000);
  }

  private function setPasswordCookie() {
    setcookie("Password", self::$password, time()+36000);
  }

  private function removeUsernameCookie() {
    setcookie("Username", NULL, time()-1);
  }

  private function removePasswordCookie() {
    setcookie("Password", NULL, time()-1);
  }

}
