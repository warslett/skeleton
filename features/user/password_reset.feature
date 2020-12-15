Feature: User Password Reset
  
  Scenario: I can reset my password
    Given that "the user" is a User with the following properties:
      | email    | john@acme.co |
    When I start a new browser session
    And I follow "Forgot password?"
    Then the title should be "Forgotten Password » Application"
    When I fill in "Email" with "john@acme.co"
    And I press "Continue"
    Then the title should be "Log in » Application"
    And there is an alert with the message "Please check your emails to reset your password"
    And "the password reset notification" is an email sent to "john@acme.co" with the subject "Reset your password"
    And "the password reset page" is a link in "the password reset notification" email labeled "Reset your password"
    When I follow "the password reset page" link
    Then the title should be "Reset your password » Application"
    When I fill in "Password" with "Password1!"
    And I fill in "Repeat Password" with "Password1!"
    And I press "Register"
    Then the title should be "Log in » Application"
    And there is an alert with the message "Successfully changed password for john@acme.co"
    When I fill in "Email" with "john@acme.co"
    And I fill in "Password" with "Password1!"
    And I press "Log in"
    Then the title should be "Dashboard » Application"

  Scenario: I cannot reset my password using a token that has expired
    Given that "the user" is a User with the following properties:
      | email    | john@acme.co |
    When I start a new browser session
    And I spoof the date for the session to "2020-01-01 09:00:00"
    And I follow "Forgot password?"
    Then the title should be "Forgotten Password » Application"
    When I fill in "Email" with "john@acme.co"
    And I press "Continue"
    Then the title should be "Log in » Application"
    And there is an alert with the message "Please check your emails to reset your password"
    And "the password reset notification" is an email sent to "john@acme.co" with the subject "Reset your password"
    And "the password reset page" is a link in "the password reset notification" email labeled "Reset your password"
    When I spoof the date for the session to "2020-01-02 09:00:01"
    And I follow "the password reset page" link
    Then the title should be "Forgotten Password » Application"
    And there is an alert with the message "Your password reset link has expired, please try again"

  Scenario: I receive a notification if I request a password reset for an account that does not exist
    When I start a new browser session
    And I follow "Forgot password?"
    Then the title should be "Forgotten Password » Application"
    When I fill in "Email" with "john@acme.co"
    And I press "Continue"
    Then the title should be "Log in » Application"
    And there is an alert with the message "Please check your emails to reset your password"
    And "the account not found notification" is an email sent to "john@acme.co" with the subject "Your email address is not registered"
