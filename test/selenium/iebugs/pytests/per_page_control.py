from selenium import selenium
import unittest, time, re
from base_test import BaseTestCase

class per_page_control(BaseTestCase):
   def setUp(self):
       self.verificationErrors = []
       self.createContext()

   def test_per_page_control(self):
       sel = self.selenium        sel.open("/")
        sel.click("css=img[alt=op5 Monitor: Log in]")
        sel.wait_for_page_to_load("30000")
        sel.click("link=Host detail")
        sel.wait_for_page_to_load("30000")
        sel.click("//div[@id='status_host']/form[3]/fieldset/input")
        sel.type("//div[@id='status_host']/form[3]/fieldset/input", "5")
        sel.key_press("//div[@id='status_host']/form[3]/fieldset/input", "\\13")
        sel.wait_for_page_to_load("30000")
        sel.click("host|beta.int.op5.se")
        try: self.assertEqual("6", sel.get_css_count("css=#host_table tbody tr"))
        except AssertionError, e: self.verificationErrors.append(str(e))
        try: self.assertEqual("5", sel.get_value("css=.items_per_page"))
        except AssertionError, e: self.verificationErrors.append(str(e))
        try: self.assertEqual("5", sel.get_value("//div[@id='status_host']/form[3]/fieldset/input"))
        except AssertionError, e: self.verificationErrors.append(str(e))
    
    def tearDown(self):
        self.selenium.stop()
        self.assertEqual([], self.verificationErrors)

if __name__ == "__main__":
    unittest.main()
