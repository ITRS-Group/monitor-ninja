from selenium import selenium
import unittest, time, re
from base_test import BaseTestCase

class Process_info(BaseTestCase):
    def setUp(self):
        self.verificationErrors = []
        self.createContext()
    
    def test_process_info(self):
        sel = self.selenium
        sel.open("/ninja/index.php/extinfo/show_process_info")
        self.failUnless(sel.is_element_present("menu"))
    
    def tearDown(self):
        self.selenium.stop()
        self.assertEqual([], self.verificationErrors)

if __name__ == "__main__":
    unittest.main()
