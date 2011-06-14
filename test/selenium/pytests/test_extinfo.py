from selenium import selenium
import unittest, time, re
from base_test import BaseTestCase

class Extinfo(BaseTestCase):
    def setUp(self):
        self.verificationErrors = []
        self.createContext()
    
    def test_extinfo(self):
        sel = self.selenium
        sel.open("/ninja/index.php/status/host")
        sel.click("link=monitor")
        sel.wait_for_page_to_load("30000")
        self.failUnless(sel.is_element_present("menu"))
    
    def tearDown(self):
        self.selenium.stop()
        self.assertEqual([], self.verificationErrors)

if __name__ == "__main__":
    unittest.main()
