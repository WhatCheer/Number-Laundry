import json

# This program takes Twilio's rate data (in JSON form) and writes out queries for inserting.
# The output (at this time) is almost direct insert, there are just a few manual tweaks
# you will run into when you try the insert.

print 'INSERT INTO prefixes ( id, prefix, country, country_code, twilio_rate ) VALUES '

with open( 'international-calling-rates-ajax', 'r' ) as handle:
	for line in json.loads( handle.read() ):
		for prefix in line['Prefixes'].replace( ',', '' ).split( ' ' ):
			print "( NULL, '%s', '%s', '%s', '%s' )," % ( 
				prefix.replace( "'", "''" ),
				line['Country'].replace( "'", "''"  ),
				line['IsoCountry'].replace( "'", "''" ),
				line['Price'].replace( "'", "''" )
			)

