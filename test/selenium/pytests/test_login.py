from selenium import selenium
import unittest, time, re
from base_test import BaseTestCase

class Login(BaseTestCase):
    def setUp(self):
        self.verificationErrors = []
        self.createContext()
    
    def test_login(self):
        sel = self.selenium
        sel.open("/ninja/index.php/default/show_login")
        sel.type("username", "monitor")
        sel.type("password", "monitor")
        sel.click("login")
        sel.wait_for_page_to_load("30000")
        try: self.failUnless(sel.is_text_present("About"))
        except AssertionError, e: self.verificationErrors.append(str(e))
    
    def tearDown(self):
        self.selenium.stop()
        self.assertEqual([], self.verificationErrors)

if __name__ == "__main__":
    unittest.main()
