
Response format:

HTTP/1.1 %intHttpResponseCode% %stringResponseMessage%

{
    "content": %jsonContent%,
    "outcome" : %boolOutcome%,
    "errors" : [
        { "message" : %errorMessage1% },
        { "message" : %errorMessage2% }
    ]
}


Legend:
%intHttpResponseCode% = Possible answers are:
        200 => OK
        400 => Wrong user input
        500 => Unexpected Server error (e.g. from DB)

%stringResponseMessage% = general response message (e.g. for 200 it is "OK") - can be displayed as alert title
%jsonContent% = a json object with content specific to the call
%boolOutcome% = true or false; true means the operation was successful depending on the context of the call
%errorMessage1%, %errorMessage2% =  zero or more error messages explaining the failure when outcome == false