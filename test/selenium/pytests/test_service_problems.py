from selenium import selenium
import unittest, time, re
from base_test import BaseTestCase

class Service_Problems(BaseTestCase):
    def setUp(self):
        self.verificationErrors = []
        self.createContext()
    
    def test_service__problems(self):
        sel = self.selenium
        sel.open("/ninja/index.php/status/host/all/6")
        self.failUnless(sel.is_element_present("menu"))
    
    def tearDown(self):
        self.selenium.stop()
        self.assertEqual([], self.verificationErrors)

if __name__ == "__main__":
    unittest.main()
