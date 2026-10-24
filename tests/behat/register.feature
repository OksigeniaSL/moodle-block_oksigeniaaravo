@block @block_oksigeniaaravo
Feature: See the support hours
  In order to know how my support time is spent
  As a manager of a hosting client
  I need to see the support hours block and its register

  Background:
    Given a support hours snapshot is stored

  @javascript
  Scenario: A manager sees the support hours on the dashboard
    Given I log in as "admin"
    And I turn editing mode on
    And I add the "Support hours (Oksigenia Aravo)" block
    Then I should see "Hours bank"
    And I should see "left of"
    And I should see "Interventions for"

  Scenario: The register page shows the summary and the work log
    Given I log in as "admin"
    When I am on the support hours register page
    Then I should see "By month"
    And I should see "Work log"
    And I should see "Added 12 users"
