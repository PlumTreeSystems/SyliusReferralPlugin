@simply @referral_links @simply_simple
Feature: Being able to refer others
  In order to get more customers
  As a User
  I need to be able get a referral link

  Background:
    Given the store operates on a channel identified by non-lowercase "C_STORE" code
    And the store has a customer group "distributor"
    And the store has a customer group "customer"
    And the store has a user, with role "distributor", with name "Ned Stark", with email "nedstark@mailinator.com" and with "1234" password
    And the store has a user, with role "customer", with name "Joffrey Baratheon", with email "joffreybaratheon@mailinator.com" and with "1234" password
    And the store has a product "Necronomicon"

  @ui
  Scenario: As a customer I can see referral link in my account
    Given I log in as "joffreybaratheon@mailinator.com" with "1234" password
    And I have a referral link from customer "joffreybaratheon@mailinator.com"
    When I open account dashboard
    And I follow "My Referrals"
    Then I see "that link" as a referral link

  @ui
  Scenario: As a customer I can see referral link on a specific product
    Given I log in as "joffreybaratheon@mailinator.com" with "1234" password
    And I have a referral link to the "Necronomicon" product from customer "joffreybaratheon@mailinator.com"
    When I view product "Necronomicon"
    Then I see "that link" as a referral link


  @ui
  Scenario: As a distributor I can see referral link in my account
    Given I log in as "nedstark@mailinator.com" with "1234" password
    And I have a referral link from customer "nedstark@mailinator.com"
    When I open account dashboard
    And I follow "My Referrals"
    Then I see "that link" as a referral link


  @ui
  Scenario: As a distributor I can see referral link on a specific product
    Given I log in as "nedstark@mailinator.com" with "1234" password
    And I have a referral link to the "Necronomicon" product from customer "nedstark@mailinator.com"
    When I check this product's details
    Then I see "that link" as a referral link


  @ui
  Scenario: Without logging in I can't see referral link in my account
    When I try to open account dashboard
    Then I should be at the login page

  @ui
  Scenario: Without logging in I can't see referral link on a specific product
    When I check this product's details
    Then I should not see "Referrals"

  @ui
  Scenario: Checking pagination
    Given the store has a user, with role "customer", with name "test1 test1", with email "test1@mailinator.com" and with "1234" password, which has an "nedstark@mailinator.com" enroller
    And the store has a user, with role "customer", with name "test2 test2", with email "test2@mailinator.com" and with "1234" password, which has an "nedstark@mailinator.com" enroller
    And the store has a user, with role "customer", with name "test3 test3", with email "test3@mailinator.com" and with "1234" password, which has an "nedstark@mailinator.com" enroller
    And the store has a user, with role "customer", with name "test4 test4", with email "test4@mailinator.com" and with "1234" password, which has an "nedstark@mailinator.com" enroller
    And the store has a user, with role "customer", with name "test5 test5", with email "test5@mailinator.com" and with "1234" password, which has an "nedstark@mailinator.com" enroller
    And the store has a user, with role "customer", with name "test6 test6", with email "test6@mailinator.com" and with "1234" password, which has an "nedstark@mailinator.com" enroller
    And the store has a user, with role "customer", with name "test7 test7", with email "test7@mailinator.com" and with "1234" password, which has an "nedstark@mailinator.com" enroller
    And the store has a user, with role "customer", with name "test8 test8", with email "test8@mailinator.com" and with "1234" password, which has an "nedstark@mailinator.com" enroller
    And the store has a user, with role "customer", with name "test9 test9", with email "test9@mailinator.com" and with "1234" password, which has an "nedstark@mailinator.com" enroller
    And the store has a user, with role "customer", with name "test10 test10", with email "test10@mailinator.com" and with "1234" password, which has an "nedstark@mailinator.com" enroller
    And the store has a user, with role "customer", with name "test11 test11", with email "test11@mailinator.com" and with "1234" password, which has an "nedstark@mailinator.com" enroller
    And the store has a user, with role "customer", with name "test12 test12", with email "test12@mailinator.com" and with "1234" password, which has an "nedstark@mailinator.com" enroller
    And I log in as "nedstark@mailinator.com" with "1234" password
    When I open account dashboard
    And I follow "My Referrals"
    Then I should see "test7@mailinator.com"
    And I should not see "test8@mailinator.com"
    When I change page parameter to "2"
    Then I should see "test8@mailinator.com"
    And I should not see "test7@mailinator.com"
    When I change page parameter to "3"
    Then I should see "test8@mailinator.com"
    And I should not see "test7@mailinator.com"
    When I change page parameter to "-1"
    Then I should see "test7@mailinator.com"
    And I should not see "test8@mailinator.com"
    When I change page parameter to "-Asd"
    Then I should see "test7@mailinator.com"
    And I should not see "test8@mailinator.com"
