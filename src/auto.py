import requests
import time
import json
import datetime

def log(event):
    print('Instagui :: ' + str(datetime.datetime.now().strftime('%H:%M:%S')) + ' :: ' + str(event))


s = requests.session()

# Fill your api key here
s.headers.update({'apikey':''})

url = "http://instagui.setset.fr/2bf6da4d-c367-40b4-93fe-dbb2194b7b94"

while True:
	log("Making request...")
	r = s.get(url,stream=True)
	if r.status_code == 400 or r.status_code == 404:
		log("-- ERROR WHILE MAKING REQUEST -- :: ERROR CODE :: "+str(r.status_code))
	for i in range(4):
		print('-----------------------\n')
	for line in r.iter_lines():
		if line:
			decoded_line = line.decode('utf-8')
			print(json.loads(decoded_line))
	for i in range(4):
		print('-----------------------\n')
	log("Waiting till next hour")
	sleep(3600)