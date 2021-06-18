#!/usr/bin/env python
# -*- coding: utf-8 -*-

# This file is part of DRK Testerfassung.


import sys
import pyqrcode
import random
import png
from fpdf import FPDF
import time
import os
import os.path
import datetime
sys.path.append("..")
from utils.slot import get_slot_time


FreeSans = '../utils/Schriftart/FreeSans.ttf'
FreeSansBold = '../utils/Schriftart/FreeSansBold.ttf'
Logo = '../utils/logo.png'

class PDFgenerator(FPDF):

	
	def create_page(self):
		self.add_page()
		self.add_font('GNU', '', FreeSans, uni=True)
		self.add_font('GNU', 'B', FreeSansBold, uni=True)
		self.set_font('GNU', 'B', 20)
		self.cell(10, 30, '', ln=1)
		self.multi_cell(200, 15, 'Ausstellung des digitalen Impfzertifikats',0, align='C')
		self.set_font('GNU', '', 18)
		self.cell(200,15, 'Name: ' + self.nachname + ', ' + self.vorname, ln=1)
		self.cell(200,15, 'Datum: ' + self.date.strftime("%d.%m.%Y"), ln=1)
		self.cell(200,15, 'Uhrzeit: ' + str(self.appointment), ln=1)
		self.cell(200,15, 'Ort:' + str(self.location), ln=1)
		#self.qrcode = pyqrcode.create(str(self.code), error='Q')
		#self.qrcode.png('tmp/'+str(self.code) + '.png', scale=6,quiet_zone=4)
		#self.image('tmp/'+ str(self.code) + '.png', y=100,x=140)
		#self.cell(10, 10, '', ln=1)
		#self.cell(200, 10, '%s' % (self.code), ln=1, align='C')
		self.cell(10, 15, '', ln=1)
		self.add_font('GNU', 'B', FreeSansBold, uni=True)
		self.set_font('GNU', 'B', 12)
		self.multi_cell(195, 5, 'Bringen Sie bitte ein gültiges Ausweisdokument sowie Ihren gelben Impfpass oder einen gültigen Impfnachweis mit.',0, align='C')
		#os.remove('tmp/'+str(self.code) + '.png')

	def creatPDF(self,content, location):
		self.code = content[6]
		self.slot = content[3]
		self.stunde = content[4]
		self.vorname = content[0]
		self.nachname = content[1]
		self.date = content[5]
		self.location = location
		self.appointment = get_slot_time(self.slot, self.stunde)
		self.time = datetime.date.today().strftime("%d.%m.%Y")
		self.create_page()
		self.filename = "../../Tickets/Ticket_" + str(self.code) + "_" + str(self.date) + ".pdf"
		self.output(self.filename)
		return self.filename
	

	def header(self):
		self.add_font('GNU', '', FreeSans, uni=True)
		self.set_font('GNU', '', 11)
		self.image(Logo, x=60, w=110, h=24, type='PNG')



	def footer(self):
		self.set_y(-120)
		self.add_font('GNU', 'B', FreeSansBold, uni=True)
		self.set_font('GNU', 'B', 12)
		self.multi_cell(195, 5, 'Mir ist bewusst, dass der vorsätzliche Gebrauch eines durch unrichtige Angaben erschlichenen Impfzertifikates strafbar ist und mit Freiheitsstrafe oder Geldstrafe geahndet werden kann. Zudem besteht das Risiko von Schadensersatzansprüchen. Wird ein solches unrichtiges Impfzertifikat im Ausland verwendet, kann dies weitere Sanktionen vor Ort nach sich ziehen.', 0)
		self.ln(5)
		self.add_font('GNU', 'B', FreeSansBold, uni=True)
		self.set_font('GNU', 'B', 9)
		self.cell(210, 10, 'Datenschutzinformationen:', ln=1)
		self.add_font('GNU', '', FreeSans, uni=True)
		self.set_font('GNU', '', 9)
		self.multi_cell(192, 5, 'Mit Ausstellung des digitalen Impfzertifikats erhebt der DRK-Kreisverband Odenwaldkreis e.V., Illigstr. 11, 64711 Erbach sowie die Bären Apotheke Hauptstr.27, 64711 Erbach bzw. die Elefanten Apotheke Gerhart-Hauptmann Str. 23, 64711 Erbach, personenbezogene Daten von Ihnen. Wir verarbeiten Ihren Namen, Anschrift, Geburtsdatum, den verwendeten Impfstoff sowie die Impftermine und E-Mail-Adresse. Sie haben das Recht auf Auskunft über die Sie betreffenden personenbezogenen Daten und auf Berichtigung unrichtiger Daten. Sie haben zudem das Recht auf Datenübertragbarkeit sowie auf Einschränkung der Datenverarbeitung. Ferner haben Sie das Recht, sich bei einer Aufsichtsbehörde zu beschweren. Bei Fragen können Sie sich jederzeit an unseren Datenschutzbeauftragten wenden. Um das Impfzertifikat erstellen zu können, sind wir gesetzlich verpflichtet, die personenbezogenen Daten aus der Impfdokumentation zu verarbeiten. Da wir verpflichtet sind, Ihre Identität sowie die Authentizität der Impfdokumentation nachzuprüfen, bewahren wir eine Kopie der Impfdokumentation für eine Dauer von fünf Jahren auf, um die Einhaltung der gesetzlichen Pflichten zu dokumentieren, insbesondere für den Fall der Inanspruchnahme durch Behörden. Rechtsgrundlage ist das Infektionsschutzgesetz i.V.m. den einschlägigen datenschutzrechtlichen Vorschriften. Meine Impfdokumentation wird zur Erstellung des COVID-19-Impfzertifikats an das Robert-Koch-Institut übermittelt, das das Zertifikat technisch generiert. Das Robert Koch-Institut ist gesetzlich befugt, die zur Erstellung und Bescheinigung des COVID-19-Impfzertifikats erforderlichen personenbezogenen Daten zu verarbeiten und ist insoweit alleine verantwortlich. Weitere Informationen zum Datenschutz sind auf der Webseite unter https://impfpass-odw.de/impressum.php jederzeit nachzulesen und können bei Bedarf herunterladen bzw. ausdrucken werden.', 0)
		self.ln(5)


