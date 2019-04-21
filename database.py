import json
def readFile(filename):
    content = []
    file = open(filename,"r")
    content = file.readlines()
    db = []
    for i in range(len(content)):
        db.append([])
        db[i] = content[i].split("?",1)
        db[i][0] += '?'
    return db

def writeFile(input, filename):
    content = []
    file = open(filename,"w+")
    for question, answer in input:
        file.write(question + '? ' + answer)
    file.close()

def load(filename):	
    with open(filename) as data_file:
        data = json.load(data_file)	

    return data

#mydict = load("dict.json")

def getSinonim(mydict, word):
    jawaban = [word]
    if word in mydict.keys():
        jawaban.extend(mydict[word]['sinonim'])
        return jawaban
    else:
        return jawaban

if __name__ == '__main__':
    #print(1)
    print(readFile('pertanyaan.txt'))
