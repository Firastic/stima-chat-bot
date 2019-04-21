import re
import itertools
from Sastrawi.StopWordRemover.StopWordRemoverFactory import StopWordRemoverFactory
from Sastrawi.Stemmer.StemmerFactory import StemmerFactory
import database
factory = StemmerFactory()
stemmer = factory.create_stemmer()
factory2 = StopWordRemoverFactory()
stopword = factory2.create_stop_word_remover()
# KMP akan mengembalikan persentase kebenaran terhadap apakah string S2 berada pada strirng S1
# dengan menggunakan algoritma Knuth-Morris-Pratt
def KMP(S1,S2):#S1 adalah pertanyaan dalam database, S2 adalah pertanyaan dari user
    n = len(S1)
    m = len(S2)
    fail = computeFail(S2)#Kmp match
    i = 0
    j = 0
    k = 0 # untuk menghitung jumlah kecocokan pada string
    arrKecocokan = [0]
    if(n < m):
        return 0
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
    return max(arrKecocokan)/(n)
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
def BM(S1,S2):#S1 adalah pertanyaan dalam database, S2 adalah pertanyaan dari user
    last = buildLast(S2)#Last occurence
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
    return nilaimax/n
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
# Akan mengembalikan list yang berisi % kecocokan dan list QnA
def searchWithBM(QnA,sentence):
    kecocokan = 0
    listQnA = []
    sentencebaru = stemmer.stem(stopword.remove(sentence))    
    for i in range(0,len(QnA)):
        pertanyaan = stemmer.stem(stopword.remove(QnA[i][0]))
        kecocokan = BM(pertanyaan,sentencebaru)
        if(kecocokan == 1):
            return [kecocokan,QnA[i]]
        elif kecocokan >= 0.9:
            listQnA.append([kecocokan,QnA[i]])
    #nggak tau ini bisa atau nggak
    if(len(listQnA) == 0):
        for i in range(0,len(QnA)):
            pertanyaan = stemmer.stem(stopword.remove(QnA[i][0]))
            kecocokan = BM(pertanyaan,sentencebaru)
        if(kecocokan != 0):
            listQnA.append([kecocokan,QnA[i]])
    listQnA.sort()
    return listQnA
# Akan mengembalikan list yang berisi % kecocokan dan list QnA
def searchWithKMP(QnA,sentence):
    kecocokan = 0
    listQnA = []
    sentencebaru = stemmer.stem(stopword.remove(sentence))
    for i in range(0,len(QnA)):
        pertanyaan = stemmer.stem(stopword.remove(QnA[i][0]))
        kecocokan = KMP(pertanyaan,sentencebaru)
        if(kecocokan == 1):
            listQnA.append([kecocokan,QnA[i]])
            return listQnA
        elif kecocokan >= 0.9:
            listQnA.append([kecocokan,QnA[i]])
    #nggak tau ini bisa atau nggak
    if(len(listQnA) == 0):
        for i in range(0,len(QnA)):
            pertanyaan = stemmer.stem(stopword.remove(QnA[i][0]))
            kecocokan = KMP(pertanyaan,sentencebaru)
            if(kecocokan > 0.5):
                listQnA.append([kecocokan,QnA[i]])
    listQnA.sort(reverse = True)
    return listQnA
                

def searchWithRegEx(QnA,sentence):
    kecocokan = False
    sentencebaru = stemmer.stem(stopword.remove(sentence))
    for i in range(0,len(QnA)):
        pertanyaan = stemmer.stem(stopword.remove(QnA[i][0]))
        kecocokan = regex(pertanyaan,sentencebaru)
        if(kecocokan):
            return QnA[i][1]
        else:
            return None
def getSinonimKata(pertanyaan):
    listkata = pertanyaan.split()
    listsinonim = []
    listkalimat = []
    for kata in listkata:
        listsinonim.append(database.getSinonim(kata))
    listkata = []
    for element in itertools.product(*listsinonim):
        listkata.append(element)
    for element in listkata:
        listkalimat.append(" ".join(element))
    return listkalimat

        