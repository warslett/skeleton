Feature: User Login

  Scenario: I can login as a user and then logout
    Given that "the user" is a User with the following properties:
      | email    | john@acme.co |
      | password | Password1!   |
    When I start a new browser session
    And I fill in "Email" with "john@acme.co"
    And I fill in "Password" with "Password1!"
    And I press "Log in"
    Then the title should be "Dashboard » Application"
    When I follow "Log out"
    Then the title should be "Log in » Application"

  Scenario: I cannot login with an email that does not belong to a user in the system
    When I start a new browser session
    And I fill in "Email" with "john@acme.co"
    And I fill in "Password" with "Password1!"
    And I press "Log in"
    Then the title should be "Log in » Application"
    And there is an alert with the message "Invalid credentials."

  Scenario: I cannot login with the wrong password
    Given that "the user" is a User with the following properties:
      | email    | john@acme.co |
      | password | Password1!   |
    When I start a new browser session
    And I fill in "Email" with "john@acme.co"
    And I fill in "Password" with "wRonGpASSwOrd"
    And I press "Log in"
    Then the title should be "Log in » Application"
    And there is an alert with the message "Invalid credentials."
