from selenium import selenium
import unittest, time, re
from base_test import BaseTestCase

class Host_Details(BaseTestCase):
    def setUp(self):
        self.verificationErrors = []
        self.createContext()
    
    def test_host__details(self):
        sel = self.selenium
        sel.open("/ninja/index.php/status/host")
        self.failUnless(sel.is_element_present("menu"))
    
    def tearDown(self):
        self.selenium.stop()
        self.assertEqual([], self.verificationErrors)

if __name__ == "__main__":
    unittest.main()
