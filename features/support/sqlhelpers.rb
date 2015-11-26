module SQLHelpers
  def insert_sql_data_into_table(database, tablename, asttable)
    rows = asttable.raw
    cols = rows.shift
    cols_str =  '(' + cols.join(', ') + ')'
    rows = asttable.hashes.map { |row|  '(' + cols.map{ |col|
      if row[col] == 'NULL'
        row[col]
      else
        "'#{row[col].gsub('"', '\"')}'"
      end
    }.join(', ') + ')' }.join(', ')

    `mysql -uroot #{database} -e "TRUNCATE TABLE #{tablename}"`
    `mysql -uroot #{database} -e "INSERT INTO #{tablename} #{cols_str} VALUES #{rows}"`
  end
end
