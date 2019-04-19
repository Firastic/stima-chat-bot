import re
# KMP akan mengembalikan persentase kebenaran terhadap apakah string S2 berada pada strirng S1
# dengan menggunakan algoritma Knuth-Morris-Pratt
def KMP(S1,S2):
    n = len(S1)
    m = len(S2)
    fail = computeFail(S2)
    i = 0
    j = 0
    k = 0
    arrKecocokan = []
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
    listkesamaan = []
    if(i>n-1):
        return -1
    j = m-1
    while (True):
        if(S2[j] == S1[i]):
            k+=1
            if(j==0):
                break
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
    nilaimax =max(listkesamaan)
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
if __name__ == "__main__":
    s1 = "semua hai"
    s2 = "hai semua"
    m = regex(s1,s2)
    if(m):
        print("True")