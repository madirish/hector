$(function(){
	var latlong = {};
	latlong["AF"] = {'latitude':33,'longitude':65,'name':"Afghanistan"}
	latlong["AL"] = {'latitude':41,'longitude':20,'name':"Albania"}
	latlong["DZ"] = {'latitude':28,'longitude':3,'name':"Algeria"}
	latlong["AS"] = {'latitude':-14.3333,'longitude':-170,'name':"American Samoa"}
	latlong["AD"] = {'latitude':42.5,'longitude':1.6,'name':"Andorra"}
	latlong["AO"] = {'latitude':-12.5,'longitude':18.5,'name':"Angola"}
	latlong["AI"] = {'latitude':18.25,'longitude':-63.1667,'name':"Anguilla"}
	latlong["AQ"] = {'latitude':-90,'longitude':0,'name':"Antarctica"}
	latlong["AG"] = {'latitude':17.05,'longitude':-61.8,'name':"Antigua and Barbuda"}
	latlong["AR"] = {'latitude':-34,'longitude':-64,'name':"Argentina"}
	latlong["AM"] = {'latitude':40,'longitude':45,'name':"Armenia"}
	latlong["AW"] = {'latitude':12.5,'longitude':-69.9667,'name':"Aruba"}
	latlong["AU"] = {'latitude':-27,'longitude':133,'name':"Australia"}
	latlong["AT"] = {'latitude':47.3333,'longitude':13.3333,'name':"Austria"}
	latlong["AZ"] = {'latitude':40.5,'longitude':47.5,'name':"Azerbaijan"}
	latlong["BS"] = {'latitude':24.25,'longitude':-76,'name':"Bahamas"}
	latlong["BH"] = {'latitude':26,'longitude':50.55,'name':"Bahrain"}
	latlong["BD"] = {'latitude':24,'longitude':90,'name':"Bangladesh"}
	latlong["BB"] = {'latitude':13.1667,'longitude':-59.5333,'name':"Barbados"}
	latlong["BY"] = {'latitude':53,'longitude':28,'name':"Belarus"}
	latlong["BE"] = {'latitude':50.8333,'longitude':4,'name':"Belgium"}
	latlong["BZ"] = {'latitude':17.25,'longitude':-88.75,'name':"Belize"}
	latlong["BJ"] = {'latitude':9.5,'longitude':2.25,'name':"Benin"}
	latlong["BM"] = {'latitude':32.3333,'longitude':-64.75,'name':"Bermuda"}
	latlong["BT"] = {'latitude':27.5,'longitude':90.5,'name':"Bhutan"}
	latlong["BO"] = {'latitude':-17,'longitude':-65,'name':"Bolivia, Plurinational State of"}
	latlong["BA"] = {'latitude':44,'longitude':18,'name':"Bosnia and Herzegovina"}
	latlong["BW"] = {'latitude':-22,'longitude':24,'name':"Botswana"}
	latlong["BV"] = {'latitude':-54.4333,'longitude':3.4,'name':"Bouvet Island"}
	latlong["BR"] = {'latitude':-10,'longitude':-55,'name':"Brazil"}
	latlong["IO"] = {'latitude':-6,'longitude':71.5,'name':"British Indian Ocean Territory"}
	latlong["BN"] = {'latitude':4.5,'longitude':114.6667,'name':"Brunei Darussalam"}
	latlong["BG"] = {'latitude':43,'longitude':25,'name':"Bulgaria"}
	latlong["BF"] = {'latitude':13,'longitude':-2,'name':"Burkina Faso"}
	latlong["BI"] = {'latitude':-3.5,'longitude':30,'name':"Burundi"}
	latlong["KH"] = {'latitude':13,'longitude':105,'name':"Cambodia"}
	latlong["CM"] = {'latitude':6,'longitude':12,'name':"Cameroon"}
	latlong["CA"] = {'latitude':60,'longitude':-95,'name':"Canada"}
	latlong["CV"] = {'latitude':16,'longitude':-24,'name':"Cape Verde"}
	latlong["KY"] = {'latitude':19.5,'longitude':-80.5,'name':"Cayman Islands"}
	latlong["CF"] = {'latitude':7,'longitude':21,'name':"Central African Republic"}
	latlong["TD"] = {'latitude':15,'longitude':19,'name':"Chad"}
	latlong["CL"] = {'latitude':-30,'longitude':-71,'name':"Chile"}
	latlong["CN"] = {'latitude':35,'longitude':105,'name':"China"}
	latlong["CX"] = {'latitude':-10.5,'longitude':105.6667,'name':"Christmas Island"}
	latlong["CC"] = {'latitude':-12.5,'longitude':96.8333,'name':"Cocos (Keeling) Islands"}
	latlong["CO"] = {'latitude':4,'longitude':-72,'name':"Colombia"}
	latlong["KM"] = {'latitude':-12.1667,'longitude':44.25,'name':"Comoros"}
	latlong["CG"] = {'latitude':-1,'longitude':15,'name':"Congo"}
	latlong["CD"] = {'latitude':0,'longitude':25,'name':"Congo, the Democratic Republic of the"}
	latlong["CK"] = {'latitude':-21.2333,'longitude':-159.7667,'name':"Cook Islands"}
	latlong["CR"] = {'latitude':10,'longitude':-84,'name':"Costa Rica"}
	latlong["CI"] = {'latitude':8,'longitude':-5,'name':"Côte d'Ivoire"}
	latlong["HR"] = {'latitude':45.1667,'longitude':15.5,'name':"Croatia"}
	latlong["CU"] = {'latitude':21.5,'longitude':-80,'name':"Cuba"}
	latlong["CY"] = {'latitude':35,'longitude':33,'name':"Cyprus"}
	latlong["CZ"] = {'latitude':49.75,'longitude':15.5,'name':"Czech Republic"}
	latlong["DK"] = {'latitude':56,'longitude':10,'name':"Denmark"}
	latlong["DJ"] = {'latitude':11.5,'longitude':43,'name':"Djibouti"}
	latlong["DM"] = {'latitude':15.4167,'longitude':-61.3333,'name':"Dominica"}
	latlong["DO"] = {'latitude':19,'longitude':-70.6667,'name':"Dominican Republic"}
	latlong["EC"] = {'latitude':-2,'longitude':-77.5,'name':"Ecuador"}
	latlong["EG"] = {'latitude':27,'longitude':30,'name':"Egypt"}
	latlong["SV"] = {'latitude':13.8333,'longitude':-88.9167,'name':"El Salvador"}
	latlong["GQ"] = {'latitude':2,'longitude':10,'name':"Equatorial Guinea"}
	latlong["ER"] = {'latitude':15,'longitude':39,'name':"Eritrea"}
	latlong["EE"] = {'latitude':59,'longitude':26,'name':"Estonia"}
	latlong["ET"] = {'latitude':8,'longitude':38,'name':"Ethiopia"}
	latlong["FK"] = {'latitude':-51.75,'longitude':-59,'name':"Falkland Islands (Malvinas)"}
	latlong["FO"] = {'latitude':62,'longitude':-7,'name':"Faroe Islands"}
	latlong["FJ"] = {'latitude':-18,'longitude':175,'name':"Fiji"}
	latlong["FI"] = {'latitude':64,'longitude':26,'name':"Finland"}
	latlong["FR"] = {'latitude':46,'longitude':2,'name':"France"}
	latlong["GF"] = {'latitude':4,'longitude':-53,'name':"French Guiana"}
	latlong["PF"] = {'latitude':-15,'longitude':-140,'name':"French Polynesia"}
	latlong["TF"] = {'latitude':-43,'longitude':67,'name':"French Southern Territories"}
	latlong["GA"] = {'latitude':-1,'longitude':11.75,'name':"Gabon"}
	latlong["GM"] = {'latitude':13.4667,'longitude':-16.5667,'name':"Gambia"}
	latlong["GE"] = {'latitude':42,'longitude':43.5,'name':"Georgia"}
	latlong["DE"] = {'latitude':51,'longitude':9,'name':"Germany"}
	latlong["GH"] = {'latitude':8,'longitude':-2,'name':"Ghana"}
	latlong["GI"] = {'latitude':36.1833,'longitude':-5.3667,'name':"Gibraltar"}
	latlong["GR"] = {'latitude':39,'longitude':22,'name':"Greece"}
	latlong["GL"] = {'latitude':72,'longitude':-40,'name':"Greenland"}
	latlong["GD"] = {'latitude':12.1167,'longitude':-61.6667,'name':"Grenada"}
	latlong["GP"] = {'latitude':16.25,'longitude':-61.5833,'name':"Guadeloupe"}
	latlong["GU"] = {'latitude':13.4667,'longitude':144.7833,'name':"Guam"}
	latlong["GT"] = {'latitude':15.5,'longitude':-90.25,'name':"Guatemala"}
	latlong["GG"] = {'latitude':49.5,'longitude':-2.56,'name':"Guernsey"}
	latlong["GN"] = {'latitude':11,'longitude':-10,'name':"Guinea"}
	latlong["GW"] = {'latitude':12,'longitude':-15,'name':"Guinea-Bissau"}
	latlong["GY"] = {'latitude':5,'longitude':-59,'name':"Guyana"}
	latlong["HT"] = {'latitude':19,'longitude':-72.4167,'name':"Haiti"}
	latlong["HM"] = {'latitude':-53.1,'longitude':72.5167,'name':"Heard Island and McDonald Islands"}
	latlong["VA"] = {'latitude':41.9,'longitude':12.45,'name':"Holy See (Vatican City State)"}
	latlong["HN"] = {'latitude':15,'longitude':-86.5,'name':"Honduras"}
	latlong["HK"] = {'latitude':22.25,'longitude':114.1667,'name':"Hong Kong"}
	latlong["HU"] = {'latitude':47,'longitude':20,'name':"Hungary"}
	latlong["IS"] = {'latitude':65,'longitude':-18,'name':"Iceland"}
	latlong["IN"] = {'latitude':20,'longitude':77,'name':"India"}
	latlong["ID"] = {'latitude':-5,'longitude':120,'name':"Indonesia"}
	latlong["IR"] = {'latitude':32,'longitude':53,'name':"Iran, Islamic Republic of"}
	latlong["IQ"] = {'latitude':33,'longitude':44,'name':"Iraq"}
	latlong["IE"] = {'latitude':53,'longitude':-8,'name':"Ireland"}
	latlong["IM"] = {'latitude':54.23,'longitude':-4.55,'name':"Isle of Man"}
	latlong["IL"] = {'latitude':31.5,'longitude':34.75,'name':"Israel"}
	latlong["IT"] = {'latitude':42.8333,'longitude':12.8333,'name':"Italy"}
	latlong["JM"] = {'latitude':18.25,'longitude':-77.5,'name':"Jamaica"}
	latlong["JP"] = {'latitude':36,'longitude':138,'name':"Japan"}
	latlong["JE"] = {'latitude':49.21,'longitude':-2.13,'name':"Jersey"}
	latlong["JO"] = {'latitude':31,'longitude':36,'name':"Jordan"}
	latlong["KZ"] = {'latitude':48,'longitude':68,'name':"Kazakhstan"}
	latlong["KE"] = {'latitude':1,'longitude':38,'name':"Kenya"}
	latlong["KI"] = {'latitude':1.4167,'longitude':173,'name':"Kiribati"}
	latlong["KP"] = {'latitude':40,'longitude':127,'name':"Korea, Democratic People's Republic of"}
	latlong["KR"] = {'latitude':37,'longitude':127.5,'name':"South Korea"}
	latlong["KW"] = {'latitude':29.3375,'longitude':47.6581,'name':"Kuwait"}
	latlong["KG"] = {'latitude':41,'longitude':75,'name':"Kyrgyzstan"}
	latlong["LA"] = {'latitude':18,'longitude':105,'name':"Lao People's Democratic Republic"}
	latlong["LV"] = {'latitude':57,'longitude':25,'name':"Latvia"}
	latlong["LB"] = {'latitude':33.8333,'longitude':35.8333,'name':"Lebanon"}
	latlong["LS"] = {'latitude':-29.5,'longitude':28.5,'name':"Lesotho"}
	latlong["LR"] = {'latitude':6.5,'longitude':-9.5,'name':"Liberia"}
	latlong["LY"] = {'latitude':25,'longitude':17,'name':"Libyan Arab Jamahiriya"}
	latlong["LI"] = {'latitude':47.1667,'longitude':9.5333,'name':"Liechtenstein"}
	latlong["LT"] = {'latitude':56,'longitude':24,'name':"Lithuania"}
	latlong["LU"] = {'latitude':49.75,'longitude':6.1667,'name':"Luxembourg"}
	latlong["MO"] = {'latitude':22.1667,'longitude':113.55,'name':"Macao"}
	latlong["MK"] = {'latitude':41.8333,'longitude':22,'name':"Macedonia, the former Yugoslav Republic of"}
	latlong["MG"] = {'latitude':-20,'longitude':47,'name':"Madagascar"}
	latlong["MW"] = {'latitude':-13.5,'longitude':34,'name':"Malawi"}
	latlong["MY"] = {'latitude':2.5,'longitude':112.5,'name':"Malaysia"}
	latlong["MV"] = {'latitude':3.25,'longitude':73,'name':"Maldives"}
	latlong["ML"] = {'latitude':17,'longitude':-4,'name':"Mali"}
	latlong["MT"] = {'latitude':35.8333,'longitude':14.5833,'name':"Malta"}
	latlong["MH"] = {'latitude':9,'longitude':168,'name':"Marshall Islands"}
	latlong["MQ"] = {'latitude':14.6667,'longitude':-61,'name':"Martinique"}
	latlong["MR"] = {'latitude':20,'longitude':-12,'name':"Mauritania"}
	latlong["MU"] = {'latitude':-20.2833,'longitude':57.55,'name':"Mauritius"}
	latlong["YT"] = {'latitude':-12.8333,'longitude':45.1667,'name':"Mayotte"}
	latlong["MX"] = {'latitude':23,'longitude':-102,'name':"Mexico"}
	latlong["FM"] = {'latitude':6.9167,'longitude':158.25,'name':"Micronesia, Federated States of"}
	latlong["MD"] = {'latitude':47,'longitude':29,'name':"Moldova, Republic of"}
	latlong["MC"] = {'latitude':43.7333,'longitude':7.4,'name':"Monaco"}
	latlong["MN"] = {'latitude':46,'longitude':105,'name':"Mongolia"}
	latlong["ME"] = {'latitude':42,'longitude':19,'name':"Montenegro"}
	latlong["MS"] = {'latitude':16.75,'longitude':-62.2,'name':"Montserrat"}
	latlong["MA"] = {'latitude':32,'longitude':-5,'name':"Morocco"}
	latlong["MZ"] = {'latitude':-18.25,'longitude':35,'name':"Mozambique"}
	latlong["MM"] = {'latitude':22,'longitude':98,'name':"Myanmar"}
	latlong["NA"] = {'latitude':-22,'longitude':17,'name':"Namibia"}
	latlong["NR"] = {'latitude':-0.5333,'longitude':166.9167,'name':"Nauru"}
	latlong["NP"] = {'latitude':28,'longitude':84,'name':"Nepal"}
	latlong["NL"] = {'latitude':52.5,'longitude':5.75,'name':"Netherlands"}
	latlong["AN"] = {'latitude':12.25,'longitude':-68.75,'name':"Netherlands Antilles"}
	latlong["NC"] = {'latitude':-21.5,'longitude':165.5,'name':"New Caledonia"}
	latlong["NZ"] = {'latitude':-41,'longitude':174,'name':"New Zealand"}
	latlong["NI"] = {'latitude':13,'longitude':-85,'name':"Nicaragua"}
	latlong["NE"] = {'latitude':16,'longitude':8,'name':"Niger"}
	latlong["NG"] = {'latitude':10,'longitude':8,'name':"Nigeria"}
	latlong["NU"] = {'latitude':-19.0333,'longitude':-169.8667,'name':"Niue"}
	latlong["NF"] = {'latitude':-29.0333,'longitude':167.95,'name':"Norfolk Island"}
	latlong["MP"] = {'latitude':15.2,'longitude':145.75,'name':"Northern Mariana Islands"}
	latlong["NO"] = {'latitude':62,'longitude':10,'name':"Norway"}
	latlong["OM"] = {'latitude':21,'longitude':57,'name':"Oman"}
	latlong["PK"] = {'latitude':30,'longitude':70,'name':"Pakistan"}
	latlong["PW"] = {'latitude':7.5,'longitude':134.5,'name':"Palau"}
	latlong["PS"] = {'latitude':32,'longitude':35.25,'name':"Palestinian Territory, Occupied"}
	latlong["PA"] = {'latitude':9,'longitude':-80,'name':"Panama"}
	latlong["PG"] = {'latitude':-6,'longitude':147,'name':"Papua New Guinea"}
	latlong["PY"] = {'latitude':-23,'longitude':-58,'name':"Paraguay"}
	latlong["PE"] = {'latitude':-10,'longitude':-76,'name':"Peru"}
	latlong["PH"] = {'latitude':13,'longitude':122,'name':"Philippines"}
	latlong["PN"] = {'latitude':-24.7,'longitude':-127.4,'name':"Pitcairn"}
	latlong["PL"] = {'latitude':52,'longitude':20,'name':"Poland"}
	latlong["PT"] = {'latitude':39.5,'longitude':-8,'name':"Portugal"}
	latlong["PR"] = {'latitude':18.25,'longitude':-66.5,'name':"Puerto Rico"}
	latlong["QA"] = {'latitude':25.5,'longitude':51.25,'name':"Qatar"}
	latlong["RE"] = {'latitude':-21.1,'longitude':55.6,'name':"Réunion"}
	latlong["RO"] = {'latitude':46,'longitude':25,'name':"Romania"}
	latlong["RU"] = {'latitude':60,'longitude':100,'name':"Russia"}
	latlong["RW"] = {'latitude':-2,'longitude':30,'name':"Rwanda"}
	latlong["SH"] = {'latitude':-15.9333,'longitude':-5.7,'name':"Saint Helena, Ascension and Tristan da Cunha"}
	latlong["KN"] = {'latitude':17.3333,'longitude':-62.75,'name':"Saint Kitts and Nevis"}
	latlong["LC"] = {'latitude':13.8833,'longitude':-61.1333,'name':"Saint Lucia"}
	latlong["PM"] = {'latitude':46.8333,'longitude':-56.3333,'name':"Saint Pierre and Miquelon"}
	latlong["VC"] = {'latitude':13.25,'longitude':-61.2,'name':"Saint Vincent and the Grenadines"}
	latlong["WS"] = {'latitude':-13.5833,'longitude':-172.3333,'name':"Samoa"}
	latlong["SM"] = {'latitude':43.7667,'longitude':12.4167,'name':"San Marino"}
	latlong["ST"] = {'latitude':1,'longitude':7,'name':"Sao Tome and Principe"}
	latlong["SA"] = {'latitude':25,'longitude':45,'name':"Saudi Arabia"}
	latlong["SN"] = {'latitude':14,'longitude':-14,'name':"Senegal"}
	latlong["RS"] = {'latitude':44,'longitude':21,'name':"Serbia"}
	latlong["SC"] = {'latitude':-4.5833,'longitude':55.6667,'name':"Seychelles"}
	latlong["SL"] = {'latitude':8.5,'longitude':-11.5,'name':"Sierra Leone"}
	latlong["SG"] = {'latitude':1.3667,'longitude':103.8,'name':"Singapore"}
	latlong["SK"] = {'latitude':48.6667,'longitude':19.5,'name':"Slovakia"}
	latlong["SI"] = {'latitude':46,'longitude':15,'name':"Slovenia"}
	latlong["SB"] = {'latitude':-8,'longitude':159,'name':"Solomon Islands"}
	latlong["SO"] = {'latitude':10,'longitude':49,'name':"Somalia"}
	latlong["ZA"] = {'latitude':-29,'longitude':24,'name':"South Africa"}
	latlong["GS"] = {'latitude':-54.5,'longitude':-37,'name':"South Georgia and the South Sandwich Islands"}
	latlong["ES"] = {'latitude':40,'longitude':-4,'name':"Spain"}
	latlong["LK"] = {'latitude':7,'longitude':81,'name':"Sri Lanka"}
	latlong["SD"] = {'latitude':15,'longitude':30,'name':"Sudan"}
	latlong["SR"] = {'latitude':4,'longitude':-56,'name':"Suriname"}
	latlong["SJ"] = {'latitude':78,'longitude':20,'name':"Svalbard and Jan Mayen"}
	latlong["SZ"] = {'latitude':-26.5,'longitude':31.5,'name':"Swaziland"}
	latlong["SE"] = {'latitude':62,'longitude':15,'name':"Sweden"}
	latlong["CH"] = {'latitude':47,'longitude':8,'name':"Switzerland"}
	latlong["SY"] = {'latitude':35,'longitude':38,'name':"Syrian Arab Republic"}
	latlong["TW"] = {'latitude':23.5,'longitude':121,'name':"Taiwan, Province of China"}
	latlong["TJ"] = {'latitude':39,'longitude':71,'name':"Tajikistan"}
	latlong["TZ"] = {'latitude':-6,'longitude':35,'name':"Tanzania, United Republic of"}
	latlong["TH"] = {'latitude':15,'longitude':100,'name':"Thailand"}
	latlong["TL"] = {'latitude':-8.55,'longitude':125.5167,'name':"Timor-Leste"}
	latlong["TG"] = {'latitude':8,'longitude':1.1667,'name':"Togo"}
	latlong["TK"] = {'latitude':-9,'longitude':-172,'name':"Tokelau"}
	latlong["TO"] = {'latitude':-20,'longitude':-175,'name':"Tonga"}
	latlong["TT"] = {'latitude':11,'longitude':-61,'name':"Trinidad and Tobago"}
	latlong["TN"] = {'latitude':34,'longitude':9,'name':"Tunisia"}
	latlong["TR"] = {'latitude':39,'longitude':35,'name':"Turkey"}
	latlong["TM"] = {'latitude':40,'longitude':60,'name':"Turkmenistan"}
	latlong["TC"] = {'latitude':21.75,'longitude':-71.5833,'name':"Turks and Caicos Islands"}
	latlong["TV"] = {'latitude':-8,'longitude':178,'name':"Tuvalu"}
	latlong["UG"] = {'latitude':1,'longitude':32,'name':"Uganda"}
	latlong["UA"] = {'latitude':49,'longitude':32,'name':"Ukraine"}
	latlong["AE"] = {'latitude':24,'longitude':54,'name':"United Arab Emirates"}
	latlong["GB"] = {'latitude':54,'longitude':-2,'name':"United Kingdom"}
	latlong["US"] = {'latitude':38,'longitude':-97,'name':"United States of America"}
	latlong["UM"] = {'latitude':19.2833,'longitude':166.6,'name':"United States Minor Outlying Islands"}
	latlong["UY"] = {'latitude':-33,'longitude':-56,'name':"Uruguay"}
	latlong["UZ"] = {'latitude':41,'longitude':64,'name':"Uzbekistan"}
	latlong["VU"] = {'latitude':-16,'longitude':167,'name':"Vanuatu"}
	latlong["VE"] = {'latitude':8,'longitude':-66,'name':"Venezuela, Bolivarian Republic of"}
	latlong["VN"] = {'latitude':16,'longitude':106,'name':"Viet Nam"}
	latlong["VG"] = {'latitude':18.5,'longitude':-64.5,'name':"Virgin Islands, British"}
	latlong["VI"] = {'latitude':18.3333,'longitude':-64.8333,'name':"Virgin Islands, U.S."}
	latlong["WF"] = {'latitude':-13.3,'longitude':-176.2,'name':"Wallis and Futuna"}
	latlong["EH"] = {'latitude':24.5,'longitude':-13,'name':"Western Sahara"}
	latlong["YE"] = {'latitude':15,'longitude':48,'name':"Yemen"}
	latlong["ZM"] = {'latitude':-15,'longitude':30,'name':"Zambia"}
	latlong["ZW"] = {'latitude':-20,'longitude':30,'name':"Zimbabwe"}
	latlong[""] = {'latitude':0,'longitude':0,'name':"Unknown"}	




	var data = $.parseJSON($('#kojoney-map-counts').text());
	var markers = [];
	var markerValues = [];
	for (iso in data){
		loc = [latlong[iso]["latitude"],latlong[iso]["longitude"]];
		val = data[iso]
		country = latlong[iso]["name"];
		markers.push({latLng:loc,value:val,name:country,code:iso});
		markerValues.push(val);
	}
	
	$('#kojoney-worldmap').vectorMap({
		map: 'world_mill_en',
		series:{
			markers: [{
		        attribute: 'r',
		        scale: [5, 15],
		        values: markerValues,
		      }]
		},
		 markers:markers,
		 scaleColors: ['#C8EEFF', '#0071A4'],
		 markerStyle: {
			 initial: {
				 fill: '#FF0F00',
				 stroke: '#FF0F00'
			 }
		 },
		 regionStyle: {
		      initial: {
		        fill: '#B8E186'
		      },
		 },
		 backgroundColor: '#C8EEFF',
		 onMarkerLabelShow: function(event,label,index){
			 label.html(
					 "<b>" + markers[index]['name'] + "</b><br/>"
					 + 'Login attempts: ' + markers[index]['value']
					 );
		 },
		 onMarkerClick: function(event,index){
			 location.href = "?action=honeypot&country=" + markers[index]['code'];
		 }
	})
})



