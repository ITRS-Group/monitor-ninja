Then /^I should see this status:$/ do |table|
  cols = table.transpose.raw
  cols.each do |row|
    title = row.shift
    all(:xpath, "//div[@id='filter_result']/table/tbody/tr/td[count(preceding-sibling::td) = count(../../../thead[position()=last()]/tr/th[contains(.,'" + title + "')]/preceding-sibling::th)]").each do |col|
      expected = row.shift
      col.should have_content expected
    end
    row.length.should be == 0
  end
end
