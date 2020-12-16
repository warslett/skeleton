Feature: Blog Posts

  Scenario: I can create blog posts
    Given that "the user" is a User with the following properties:
      | email | john@acme.co |
      | password | development |
    When I start a new browser session
    And I fill in "Email" with "john@acme.co"
    And I fill in "Password" with "development"
    And I press "Log in"
    And I go to "/blog/posts/create"
    And I fill in "Title" with "My Blog Post"
    And I fill in "Content" with "Lorem Ipsum"
    And I press "Save"
    Then the title should be "Dashboard » Application"
    And there is an alert with the message 'Successfully created "My Blog Post"'
