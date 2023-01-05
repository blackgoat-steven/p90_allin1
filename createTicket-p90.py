#!/usr/bin/env python

import requests,json, sys, socket
from datetime import datetime as dt

import logging, traceback
#logging.basicConfig(level=logging.INFO, 
logging.basicConfig(level=logging.DEBUG,
    format='%(asctime)s %(name)-12s %(levelname)-8s %(message)s',
    filename='/tmp/itop_rest.log', filemode='a')

## itop parameters
ITOP_LIST = ['http://10.64.68.20/igms_sit']
ITOP_USER = 'admin'
ITOP_PWD = 'unix11'
ORG = 'FET-IT'
CALLER = 'IIMS'
TITLE = '%(host)s: service \"%(service)s\" is %(service_status)s'
DESCRIPTION = '%(service_output)s'
h =  socket.gethostname()
COMMENT = 'auto-%(op)s from P90 ('+h+')'
ASSIGN_TEAM='System Administrator'

logging.info("Running command with argvs: '" + "' '".join(map(str,sys.argv)) + "'") 
if len(sys.argv) < 6:
    print "Usage:\n"
    print "\t for Host: "+sys.argv[0]+" host host_status host_state_type host_output\n"
    print "\t for Service: "+sys.argv[0]+" host service service_status service_state_type service_output target_name\n"
    logging.warning("Usage: "+sys.argv[0]+" host service service_status service_state_type service_output")
    sys.exit(0)
else:
    host = sys.argv[1]
    if sys.argv[2] == 'UP' or sys.argv[2] == 'DOWN':
        service = sys.argv[1]
        service_status = sys.argv[2]
        service_state_type = sys.argv[3]
        service_output = sys.argv[4]
        escalation_reason = "DOWN"
        TITLE = '%(host)s: host \"%(service)s\"'
    else:
        service = sys.argv[2]
        service_status = sys.argv[3]
        service_state_type = sys.argv[4]
        service_output = sys.argv[5]
        target_name = sys.argv[6]
        escalation_reason = service
        TITLE = '%(host)s: rule \"%(service)s\" [%(target_name)s]'

if service_state_type != 'HARD':
    logging.info("Service state type("+service_state_type+") is not what we expect(HARD), doing nothing") 
    sys.exit(0)

for ITOP_URL in ITOP_LIST:
  try:
    logging.info("Processing %(itop)s" % {'itop':ITOP_URL})
    logging.info("Query if there is already an old ticket with host %(host)s and service %(service)s" % {'host':host, 'service':service})
    ## check if there is already a ticket with same title
    json_get = {
        'operation': 'core/get',
        'class': 'Incident',
        'key': 'SELECT Ticket WHERE title LIKE \'%(host)s: rule \"%(service)s\" [%(target_name)s]%%\' AND operational_status = \'ongoing\' AND finalclass = \'incident\'' % {'host': host, 'service': service, 'target_name': target_name},
        'output_fields': "id, ref, start_date",
#        'output_fields': "*",
    }
    sql = 'SELECT Ticket WHERE title LIKE \'%(host)s: rule \"%(service)s\" [%(target_name)s]%%\' AND operational_status = \'ongoing\' AND finalclass = \'incident\'' % {'host': host, 'service': service, 'target_name': target_name}
    logging.info("query key:" + str(sql))
    encoded_get = json.dumps(json_get)
    r = requests.post(ITOP_URL+'/webservices/rest.php?version=1.0', verify=False, data={'auth_user': ITOP_USER, 'auth_pwd': ITOP_PWD, 'json_data': encoded_get})
    rs = json.loads(r.text)
    logging.info(rs['message'])
    logging.debug("return json: " + json.dumps(rs, indent=4))
    if rs['code'] == 0:
        if rs['message'] == "Found: 0":
            oTicket = None
        else:
            oTicket = rs['objects'][rs['objects'].keys()[0]]['fields']['ref']
            oKeyid = rs['objects'][rs['objects'].keys()[0]]['fields']['id']
#           oStartDate = datetime.strptime(rs['objects'][rs['objects'].keys()[0]]['fields']['start_date'], '%Y-%m-%d %H:%M:%S')
    else:
        logging.error("rest api exception return: " + rs['message'])
        sys.exit(3)


    ## action with service status
    ## Critical or Down and no oTicket --> create new ticket
    ## OK or UP and oTicket --> close ticket
    logging.info("service_status: " + str(service_status))
    if service_status == "1" or service_status == "critical": 
        if oTicket is None:
            json_create = {
                'operation': 'core/create',
                'class': 'Incident',
                'fields': {
                    'title': TITLE % {'host': host, 'service': service, 'target_name': target_name},
                    'description': DESCRIPTION % {'service_output': service_output},
                    'org_id': 'SELECT Organization WHERE name LIKE \'%%%(org)s%%\'' % {'org': ORG},
                    'caller_id': 'SELECT Contact WHERE name="%(caller)s"' % {'caller': CALLER},
                    'origin': 'monitoring',
                    'impact': "2",
                    'escalation_reason': escalation_reason,
                },
                'comment': COMMENT % {'op': "create"},
                'output_fields': 'ref, title',
            }

            # Get FunctionalCI id from hostname
            logging.info("Query if there is any FunctionalCI link with host %(host)s" % {'host':host})
            json_get = {
                'operation': 'core/get',
                'class': 'FunctionalCI',
                'key': "SELECT FunctionalCI WHERE name='%(host)s'" % {'host': host},
                'output_fields': "id, name",
            }
            encoded_get = json.dumps(json_get)
            r = requests.post(ITOP_URL+'/webservices/rest.php?version=1.0', verify=False, data={'auth_user': ITOP_USER, 'auth_pwd': ITOP_PWD, 'json_data': encoded_get})
            result = json.loads(r.text)
            logging.debug("Push json to query: " + json.dumps(json_get, indent=4))
            logging.info(result['message'])
            logging.debug("return json: " + json.dumps(result, indent=4))

            try:
                host_id = None
                host_id = result['objects'][result['objects'].keys()[0]]['fields']['id']
                json_create['fields']['functionalcis_list']= [ { 'functionalci_id': "SELECT FunctionalCI WHERE id=%(host_id)s" % {'host_id':host_id}, 'impact_code': 'manual', } ]
            except:
                json_create['fields']['description'] += '<br><br> <font color=red>No Related CI: '+host+'</font>'

            # Only set urgency and Application Service if there are functionalCI for host
            if host_id is not None:
                logging.info("Get %(host)s with CI = %(host_id)s." % {'host':host,'host_id':host_id})

                logging.info("Query to get business_criticity on host=%(host)s,CI=%(host_id)s" % {'host':host,'host_id':host_id})
                # Get Application business_criticity and set it to urgency field
                urgency = {"critical":1 , "high":2 , "medium":3 , "low":4}
                json_get = {
                    'operation': 'core/get',
                    'class': 'FunctionalCI',
                    'key': "SELECT FunctionalCI WHERE name = '%(host)s'" % {'host': host},
                    'output_fields': "business_criticity",
                }
                encoded_get = json.dumps(json_get)
                r = requests.post(ITOP_URL+'/webservices/rest.php?version=1.0', verify=False, data={'auth_user': ITOP_USER, 'auth_pwd': ITOP_PWD, 'json_data': encoded_get})
                result = json.loads(r.text)
                logging.info(result['message'])
                logging.debug("return json: " + json.dumps(result, indent=4))
                try:
                    logging.info("business_criticity="+result['objects'][result['objects'].keys()[0]]['fields']['business_criticity'])
                    json_create['fields']['urgency']= urgency[result['objects'][result['objects'].keys()[0]]['fields']['business_criticity']]
                except:
                    logging.warn("Can't get business criticity. Append to description")
                    json_create['fields']['description'] += '<br><br> No Related Business Criticity for '+host

                # Get Application Service and set it to service field
                logging.info("Query to get application service of host=%(host)s,CI=%(host_id)s" % {'host':host,'host_id':host_id})
                json_get = {
                    'operation': 'core/get',
                    'class': 'FunctionalCI',
                    'key': "SELECT Service AS S JOIN lnkFunctionalCIToService AS L ON L.service_id = S.id JOIN FunctionalCI AS F ON L.functionalci_id = F.id WHERE F.name='%(host)s'" % {'host': host},
                    'output_fields': "friendlyname, id",
                }
                encoded_get = json.dumps(json_get)
                r = requests.post(ITOP_URL+'/webservices/rest.php?version=1.0', verify=False, data={'auth_user': ITOP_USER, 'auth_pwd': ITOP_PWD, 'json_data': encoded_get})
                result = json.loads(r.text)
                logging.info(result['message'])
                logging.debug("return json: " + json.dumps(result, indent=4))
                try:
                    for s in result['objects']:
                        service_id = result['objects'][s]['key']
                        service_name = result['objects'][s]['fields']['friendlyname']
                        json_create['fields']['service_id']= service_id
                        logging.info("service=%(service_name)s (%(service_id)s)" % {'service_name':service_name,'service_id':service_id})
                except:
                    logging.warn("Can't get application service. Append to description")
                    json_create['fields']['description'] += '<br><br> No Related Service Application for '+host
            else:
                logging.info("No CI found for %(host)s. Only append 'No Related CI' to description" % {'host':host})

            ## Finally. Let's create ticket
            logging.info("Create ticket")
            logging.debug("Create ticket with json: " + json.dumps(json_create, indent=4))
            encoded_data = json.dumps(json_create)
            r = requests.post(ITOP_URL+'/webservices/rest.php?version=1.0', verify=False, data={'auth_user': ITOP_USER, 'auth_pwd': ITOP_PWD, 'json_data': encoded_data})
            result = json.loads(r.text);
            if result['code'] == 0:
                logging.info("Ticket created with Ref="+result['objects'][result['objects'].keys()[0]]['fields']['ref']+". "+result['objects'][result['objects'].keys()[0]]['fields']['title'])
            else:
                logging.error("Ticket Create with error: " + result['message'])
            logging.debug("return json: " + json.dumps(result, indent=4))
        else:
            logging.info("Ticket already there. ref = " + oTicket + ". do nothing")
            
    elif service_status == "0" or service_status == "ok":
        if oTicket:
            logging.info("Ticket " + oTicket.rstrip() + " could be close. Start processing... Assing -> Resolve -> Close")

            logging.info("Try to Assign Ticket " + oTicket.rstrip() + " to IIMS")
            json_assign={
                "operation": "core/apply_stimulus",
                "comment": COMMENT % {'op': "assign"}, 
                "class": "Incident",
                "key": oKeyid,
                "stimulus": "ev_assign",
                "output_fields": "*",
                "fields":
                {
                    "team_id": "SELECT Team WHERE name = \""+ASSIGN_TEAM+"\"",
                    "agent_id": "SELECT Person WHERE name = \"IIMS\""
                }
            }
            encoded_data = json.dumps(json_assign)
            logging.debug("Push json to assign: " + json.dumps(json_assign, indent=4))

            r = requests.post(ITOP_URL+'/webservices/rest.php?version=1.0', verify=False, data={'auth_user': ITOP_USER, 'auth_pwd': ITOP_PWD, 'json_data': encoded_data})
            response = json.loads(r.text);
            if response['code'] == 0:
                logging.info(oTicket+" is assigned to IIMS")
            else:
                logging.error("Assign Ticket with error: " + response['message'])  
                logging.error("return json: " + json.dumps(response, indent=4, sort_keys=True))

            logging.debug("Assign return json: " + json.dumps(response, indent=4, sort_keys=True))


            logging.info("Try to Resolve Ticket " + oTicket.rstrip())
            json_resolve = {
               "operation": "core/apply_stimulus",
               "comment": COMMENT % {'op': "resolve"},
               "class": "Incident",
               "key": oKeyid,
               "stimulus": "ev_resolve",
               "output_fields": "*",
               "fields":
               {
                  "resolution_code": "other",
                  "solution": "Auto Close from IIMS (" + h + ")"
               }
            }
            encoded_data = json.dumps(json_resolve)
            logging.debug("Push json to resolve: " + json.dumps(json_resolve, indent=4))

            r = requests.post(ITOP_URL+'/webservices/rest.php?version=1.0', verify=False, data={'auth_user': ITOP_USER, 'auth_pwd': ITOP_PWD, 'json_data': encoded_data})
            response = json.loads(r.text);
            if response['code'] == 0:
                logging.info(oTicket+" is assigned to IIMS")
            else:
                logging.error("Resolve Ticket with error: " + response['message'])  
                logging.error("return json: " + json.dumps(response, indent=4, sort_keys=True))

            logging.debug("Resolve return json: " + json.dumps(response, indent=4, sort_keys=True))
            
            
            logging.info("Try to close Tciket with ref = " + oTicket.rstrip())
            now=dt.now() 

            json_update = {
#                "operation": "core/apply_stimulus",
                "operation": "core/update",
                "comment": COMMENT % {'op': "close"},
                "class": "Incident",
                "key": oKeyid,
                "output_fields": "*",
#                "stimulus": "ev_close",
                "fields":
                {
                    "status": "closed",
                    "close_date": now.strftime("%Y-%m-%d %H:%M:%S"),
                    "resolution_code": "other",
                    "solution": "Auto Close from IIMS (" + h + ")",
                    "user_comment": "N/A. This is Auto Close from IIMS (" + h + ")",
#                    "escalation_reason": escalation_reason
                }
            }
            encoded_data = json.dumps(json_update)
            logging.debug("Push json to close: " + json.dumps(json_update, indent=4))

            r = requests.post(ITOP_URL+'/webservices/rest.php?version=1.0', verify=False, data={'auth_user': ITOP_USER, 'auth_pwd': ITOP_PWD, 'json_data': encoded_data})
            response = json.loads(r.text);
            if response['code'] == 0:
                logging.info(oTicket+" is closed successfully")
            else:
                logging.error("Close Ticket with error: " + response['message'])  
                logging.error("return json: " + json.dumps(response, indent=4, sort_keys=True))

            logging.debug("Close return json: " + json.dumps(response, indent=4, sort_keys=True))
        else:
            logging.info("No old ticket to close. do nothing")
    else:
        logging.info("Not expected Status. do nothing")
  except: 
    logging.exception('Got exception inside for loop')
    raise
    break

logging.info("End script...")
logging.info("-"*40)   

