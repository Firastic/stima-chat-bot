import re
from Sastrawi.StopWordRemover.StopWordRemoverFactory import StopWordRemoverFactory
from Sastrawi.Stemmer.StemmerFactory import StemmerFactory
factory = StemmerFactory()
stemmer = factory.create_stemmer()
factory2 = StopWordRemoverFactory()
stopword = factory2.create_stop_word_remover()
# KMP akan mengembalikan persentase kebenaran terhadap apakah string S2 berada pada strirng S1
# dengan menggunakan algoritma Knuth-Morris-Pratt
def readFile(filename):
    content = []
    file = open(filename,"r")
    content = file.readlines()
    db = []
    for i in range(len(content)):
        db.append([])
        db[i] = content[i].split("?")
    return db
def KMP(S1,S2):
    n = len(S1)
    m = len(S2)
    fail = computeFail(S2)
    i = 0
    j = 0
    k = 0
    arrKecocokan = [0]
    while(i<n):
        if(S2[j] == S1[i]):
            i+=1
            j+=1
            k+=1
            if(j == m):
                arrKecocokan.append(k)
                break
        elif(j>0):
            j = fail[j-1]
            arrKecocokan.append(k)
            k = 0
        else:
            i+=1 
    return max(arrKecocokan)/(m)
def computeFail(S2):
    fail = []
    m = len(S2)
    i=1
    j=0
    for x in range(0,m):
        fail.append(0)
    while(i<m):
        if(S2[j]==S2[i]):
            fail[i]= j+1
            i+=1
            j+=1
        elif(j>0):
            j = fail[j-1]
        else:
            i+=1
    return fail
# BM akan mengembalikan persentase kebenaran terhadap apakah string S2 berada pada string S1
# dengan menggunakan Boyer Moore algorithm
def BM(S1,S2):
    last = buildLast(S2)
    n = len(S1)
    m = len(S2)
    i = m-1
    k = 0
    listkesamaan = [0]
    if(i>n-1):
        return 0
    j = m-1
    while (True):
        if(S2[j] == S1[i]):
            k+=1
            if(j==0):
                return 1
            else:
                i-=1
                j-=1
        else:
            lo = last[ord(S1[i])]
            i = i + m - min(j,1+lo)
            j = m-1
            listkesamaan.append(k)
            k = 0
        if(i>n-1):
            break
    nilaimax = max(listkesamaan)
    return nilaimax/m
def buildLast(S2):
    last = []
    for i in range(0,128):
        last.append(-1)
    for i in range(0,len(S2)):
        last[ord(S2[i])] = i
    return last
# regex akan mengembalikan true atau false terhadap apakah string S2 berada pada string S1
# dengan menggunakan regular expression
def regex(S1,S2):
    list = S2.split()
    newlist = []
    exceptchar = ['[','\\','^','$','.','|','?','*','+','(',')']
    i = 0
    location = []
    for x in list:
        if(x not in exceptchar):
            newlist.append(x)
        else:
            s = '\\' + x
            newlist.append(s) 
    while(i<len(newlist)):
        m = re.search(newlist[i],S1,re.IGNORECASE)
        if(m):
            i+=1
            location.append(m.start())
        else:
            return False
    for i in range(0,len(location)):
        for j in range(i,len(location)):
            if(location[i]>location[j]):
                return False
    return True
def searchWithBM(QnA,sentence):
    kecocokan = 0
    for i in range(0,len(QnA)):
        pertanyaan = stemmer.stem(stopword.remove(QnA[i][0]))
        sentencebaru = stemmer.stem(stopword.remove(sentence))
        kecocokan = BM(pertanyaan,sentencebaru)
        if(kecocokan == 1):
            return QnA[i][1]
def searchWithKMP(QnA,sentence):
    kecocokan = 0
    for i in range(0,len(QnA)):
        pertanyaan = stemmer.stem(stopword.remove(QnA[i][0]))
        sentencebaru = stemmer.stem(stopword.remove(sentence))
        kecocokan = KMP(pertanyaan,sentencebaru)
        if(kecocokan == 1):
            return QnA[i][1]

def searchWithRegEx(QnA,sentence):
    kecocokan = 0
    for i in range(0,len(QnA)):
        pertanyaan = stemmer.stem(stopword.remove(QnA[i][0]))
        sentencebaru = stemmer.stem(stopword.remove(sentence))
        kecocokan = regex(pertanyaan,sentencebaru)
        if(kecocokan):
            return QnA[i][1]

