/***********************************************
* JavaScriptKit.com Multiple Choice Quiz Script (http://www.javascriptkit.com)
* Copyright 2003 JavaScript Kit- http://www.javascriptkit.com
* This notice and footnote must stay intact for use
* Visit JavaScript Kit (http://www.javascriptkit.com/) for full source code
***********************************************/

//Enter total number of questions:
var totalquestions=15;

//Enter the solutions corresponding to each question:
var correctchoices=new Array()
correctchoices[1]='b' //question 1 solution
correctchoices[2]='c' //question 2 solution, and so on.
correctchoices[3]='d'
correctchoices[4]='a'
correctchoices[5]='b'
correctchoices[6]='b'
correctchoices[7]='d'
correctchoices[8]='b'
correctchoices[9]='d'
correctchoices[10]='c'
correctchoices[11]='a'
correctchoices[12]='a'
correctchoices[13]='d'
correctchoices[14]='b'
correctchoices[15]='a'

/////Don't edit beyond here//////////////////////////

function gradeit(){
document.cookie="";
var incorrect=null
for (q=1;q<=totalquestions;q++){
	var thequestion=document.getElementsByName("question"+q);
	for (c=0;c<thequestion.length;c++){
		console.log(thequestion[c].value)
		if (thequestion[c].checked==true){
		actualchoices[q]=thequestion[c].value;
		}
		}
	console.log(actualchoices);
	console.log(correctchoices);
	if (actualchoices[q]!=correctchoices[q]){ //process an incorrect choice
		if (incorrect==null)
		incorrect=q
		else
		incorrect+="/"+q
		}
	}

if (incorrect==null)
incorrect="a/b"
document.cookie='q='+incorrect
if (document.cookie==''){
alert("Your browser does not accept cookies. Please adjust your browser settings.")
}
else{
window.location="results.htm";	
}
}


function showsolution(){
var win2=window.open("","win2","width=200,height=350, scrollbars")
win2.focus()
win2.document.open()
win2.document.write('<title>Solution</title>')
win2.document.write('<body bgcolor="#FFFFFF">')
win2.document.write('<center><h3>Solution to Quiz</h3></center>')
win2.document.write('<center><font face="Arial">')
for (i=1;i<=totalquestions;i++){
for (temp=0;temp<incorrect.length;temp++){
if (i==incorrect[temp])
wrong=1
}
if (wrong==1){
win2.document.write("Question "+i+"="+correctchoices[i].fontcolor("red")+"<br>")
wrong=0
}
else
win2.document.write("Question "+i+"="+correctchoices[i]+"<br>")
}
win2.document.write('</center></font>')
win2.document.write("<h5>Note: The solutions in red are the ones to the questions you had incorrectly answered.</h5><p align='center'><small><a href='http://www.javascriptkit.com' target='_new'>JavaScript Kit quiz script</a></small>")
win2.document.close()
}
