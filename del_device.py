# -*- coding: utf-8 -*-

import pika
import json
import re
import sys

data = {}
data['method'] = "del_device"
data['boxname'] = "iimspoc17"
data['hostname'] = "127.0.0.1"
data['boxname'] = sys.argv[1]
data['hostname'] = sys.argv[2]
json_data = json.dumps(data)

boxname = data['boxname']

print json_data
credentials = pika.PlainCredentials('admin', 'admin')
connection = pika.BlockingConnection(pika.ConnectionParameters('210.241.195.30', 5672, '/', credentials))
channel = connection.channel()
channel.queue_declare(queue=boxname,durable=True,passive=True)
channel.basic_publish(exchange='', routing_key=boxname, body=json_data)
connection.close()
