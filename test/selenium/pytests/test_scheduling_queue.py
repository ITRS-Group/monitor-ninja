from selenium import selenium
import unittest, time, re
from base_test import BaseTestCase

class Scheduling_queue(BaseTestCase):
    def setUp(self):
        self.verificationErrors = []
        self.createContext()
    
    def test_scheduling_queue(self):
        sel = self.selenium
        sel.click("link=Scheduling queue")
        sel.wait_for_page_to_load("30000")
        self.failUnless(sel.is_element_present("menu"))
    
    def tearDown(self):
        self.selenium.stop()
        self.assertEqual([], self.verificationErrors)

if __name__ == "__main__":
    unittest.main()
