Feature: Scheduling information
  Scenario: Remote scheduling information is hidden in extinfo
    Verify that scheduling information for a check that is run on a remote node (peer or poller)
    is presented as such on the extinfo page for a host

    Given I have these mocked hosts
      |name      |next_check|check_source      |active_checks_enabled|
      |remotehost|10        |Merlin peer Gustaf|true                 |
    And I am on the Host details page
    And I click "remotehost"
    Then I should see "Remotely checked by Gustaf"

  Scenario: Local scheduling information is visible in extinfo
    Verify that scheduling information for a check that is run on a local node
    is presented as-is on the extinfo page for a host

    Given I have these mocked hosts
      |name      |next_check  |check_source       |active_checks_enabled|
      |local_host|2147485547  |Core Worker 12     |true                 |
    And I am on the Host details page
    And I click "local_host"
    # note: 2147485547 is Tue Jan 19 03:45:47 UTC 2038
    Then I should see "2038-01"


  Scenario: Remote scheduling information is hidden in scheduling queue
    Verify that scheduling information for a check that is run on a remote node (peer or poller)
    is not included in the scheduling queue

    Given I have these mocked hosts
      |name       |next_check|check_source      |active_checks_enabled|
      |remote_host|10        |Merlin peer Gustaf|true                 |
      |local_host |10        |Core Worker 666   |true                 |
    And I am on address "/monitor/index.php/extinfo/scheduling_queue"
    Then I should see "local_host"
    But I shouldn't see "remote_host"

