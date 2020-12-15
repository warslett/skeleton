Feature: User Registration

  Scenario: I can register an account for an email address that is not registered on the system
    When I start a new browser session
    And I follow "Register for an account"
    And I fill in "Email" with "john@acme.co"
    And I press "Continue"
    Then the title should be "Log in » Application"
    And there is an alert with the message "Please follow the link in your activation email to complete registration"
    And "the email address confirmation" is an email sent to "john@acme.co" with the subject "Confirm your email address"
    And "the confirmation activation" is a link in "the email address confirmation" email labeled "Confirm your email address"
    When I follow "the confirmation activation" link
    Then the title should be "Complete Registration » Application"
    When I fill in "Password" with "Password1!"
    And I fill in "Repeat Password" with "Password1!"
    And I press "Register"
    Then the title should be "Dashboard » Application"
    And there is an alert with the message "Successfully registered john@acme.co"

  Scenario: I cannot register an account for an email address that is already registered on the system
    Given that "the user" is a User with the following properties:
      | email    | john@acme.co |
      | password | Password1!   |
    When I start a new browser session
    And I follow "Register for an account"
    And I fill in "Email" with "john@acme.co"
    And I press "Continue"
    Then "the account already registered notification" is an email sent to "john@acme.co" with the subject "Your email address is already registered"

  Scenario: I cannot complete registration for a token with an email address that is already registered in the system
    When I start a new browser session
    And I follow "Register for an account"
    And I fill in "Email" with "john@acme.co"
    And I press "Continue"
    Then "the first email address confirmation" is an email sent to "john@acme.co" with the subject "Confirm your email address"
    And "the first confirmation activation" is a link in "the first email address confirmation" email labeled "Confirm your email address"
    When I start a new browser session
    And I follow "Register for an account"
    And I fill in "Email" with "john@acme.co"
    And I press "Continue"
    Then "the second email address confirmation" is an email sent to "john@acme.co" with the subject "Confirm your email address"
    And "the second confirmation activation" is a link in "the second email address confirmation" email labeled "Confirm your email address"
    When I follow "the first confirmation activation" link
    Then the title should be "Complete Registration » Application"
    When I fill in "Password" with "Password1!"
    And I fill in "Repeat Password" with "Password1!"
    And I press "Register"
    Then the title should be "Dashboard » Application"
    And there is an alert with the message "Successfully registered john@acme.co"
    When I start a new browser session
    And I follow "the second confirmation activation" link
    Then the title should be "Log in » Application"
    And there is an alert with the message "Your email is already registered"

  Scenario: I cannot complete registration using a token that has expired
    When I start a new browser session
    And I spoof the date for the session to "2020-01-01 09:00:00"
    And I follow "Register for an account"
    And I fill in "Email" with "john@acme.co"
    And I press "Continue"
    Then "the email address confirmation" is an email sent to "john@acme.co" with the subject "Confirm your email address"
    And "the confirmation activation" is a link in "the email address confirmation" email labeled "Confirm your email address"
    When I spoof the date for the session to "2020-01-02 09:00:01"
    And I follow "the confirmation activation" link
    Then the title should be "Register » Application"
    And there is an alert with the message "Your registration has expired, please try again"
