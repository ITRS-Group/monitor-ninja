from selenium import selenium
import unittest, time, re
from base_test import BaseTestCase

class Network_outages(BaseTestCase):
    def setUp(self):
        self.verificationErrors = []
        self.createContext()
    
    def test_network_outages(self):
        sel = self.selenium
        sel.open("/ninja/index.php/outages")
        self.failUnless(sel.is_element_present("menu"))
    
    def tearDown(self):
        self.selenium.stop()
        self.assertEqual([], self.verificationErrors)

if __name__ == "__main__":
    unittest.main()
