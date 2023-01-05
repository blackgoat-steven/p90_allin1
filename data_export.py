import sys
import MySQLdb
from xlsxwriter.workbook import Workbook

user = 'root' # your username
passwd = 'k7Pdg_vX' # your password
host = 'localhost' # your host
db = 'iimspoc17' # database where your table is stored
#table = '' # table you want to save

db = sys.argv[1]
report = sys.argv[2]

con = MySQLdb.connect(user=user, passwd=passwd, host=host, db=db)
cursor = con.cursor()
if len(sys.argv) < 2:
  print "FAIL"
  exit(2)
filename = "html/download/" + report + ".xlsx"
#print filename
workbook = Workbook(filename)
worksheet = workbook.add_worksheet()

if report == "mac":
  query = "select sysName,ifName,ifDescr,ifPhysAddress,ifAlias FROM `ports` AS I, `devices` AS D WHERE I.device_id = D.device_id;"
#  print query
  bold = workbook.add_format({'bold': True})
  worksheet.write('A1', 'Sysname', bold)
  worksheet.write('B1', 'IfName', bold)
  worksheet.write('C1', 'IfDescr', bold)
  worksheet.write('D1', 'ifPhysAddress', bold)
  worksheet.write('E1', 'ifAlias', bold)
elif report == "ipv4":
  query = "select sysName,ifName,ipv4_address,ifAlias FROM `ipv4_addresses` AS A, `ports` AS I, `ipv4_networks` AS N, `devices` AS D WHERE I.port_id = A.port_id AND I.device_id = D.device_id AND N.ipv4_network_id = A.ipv4_network_id;"
#  print query
  bold = workbook.add_format({'bold': True})
  worksheet.write('A1', 'Sysname', bold)
  worksheet.write('B1', 'IfName', bold)
  worksheet.write('C1', 'IPaddress', bold)
  worksheet.write('D1', 'ifAlias', bold)
elif report == "inventory":
  query = "SELECT sysName,`entPhysicalDescr` AS `description`, `entPhysicalName` AS `name`, `entPhysicalModelName` AS `model`, `entPhysicalSerialNum` AS `serial`  FROM entPhysical AS E, devices AS D WHERE 1 AND D.device_id = E.device_id;"
#  print query
  bold = workbook.add_format({'bold': True})
  worksheet.write('A1', 'Sysname', bold)
  worksheet.write('B1', 'Description', bold)
  worksheet.write('C1', 'Name', bold)
  worksheet.write('D1', 'Part NO', bold)
  worksheet.write('D1', 'Serial NO', bold)
elif report == "ports":
  query = "SELECT sysName,ifName,ifLastChange,ifHighSpeed,ifOperStatus,ifAdminStatus,ifType,ifAlias FROM `ports` AS I, `devices` AS D LEFT JOIN `locations` AS L ON D.location_id = L.id WHERE I.device_id = D.device_id AND `I`.`ignore` = 0 AND `I`.`disabled` = 0 AND `I`.`deleted` = 0;"
#  print query
  bold = workbook.add_format({'bold': True})
  worksheet.write('A1', 'Sysname', bold)
  worksheet.write('B1', 'Ifname', bold)
  worksheet.write('C1', 'Last status change', bold)
  worksheet.write('D1', 'Speed', bold)
  worksheet.write('E1', 'Operation status', bold)
  worksheet.write('F1', 'Admin status', bold)
  worksheet.write('G1', 'Media type', bold)
  worksheet.write('H1', 'If description', bold)
else:
  exit(2)
cursor.execute(query)
results = cursor.fetchall()
#print results

for r, row in enumerate(results):
    for c, col in enumerate(row):
#        print "r=" + str(r)
#        print "c=" + str(c)
#        print "col=" + str(col)
        worksheet.write(r+1, c, col)
print "OK"
workbook.close()
