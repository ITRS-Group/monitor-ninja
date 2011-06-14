from selenium import selenium
import unittest, time, re
from base_test import BaseTestCase

class Service_details(BaseTestCase):
    def setUp(self):
        self.verificationErrors = []
        self.createContext()
    
    def test_service_details(self):
        sel = self.selenium
        sel.open("/ninja/index.php/status/service")
        self.failUnless(sel.is_element_present("service_table"))
    
    def tearDown(self):
        self.selenium.stop()
        self.assertEqual([], self.verificationErrors)

if __name__ == "__main__":
    unittest.main()
