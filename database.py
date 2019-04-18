import json

def readFile(filename):
    content = []
    file = open(filename,"r")
    content = file.readlines()
    db = []
    for i in range(len(content)):
        db.append([])
        db[i] = content[i].split("?")
    return db

def load(filename):	
    with open(filename) as data_file:
        data = json.load(data_file)	

    return data

mydict = load("kamus.json")

def getSinonim(word):
    if word in mydict.keys():
        return mydict[word]['sinonim']
    else:
        return []


QnA = readFile("pertanyaan.txt")
print(QnA)

print(getSinonim('pemenang'))