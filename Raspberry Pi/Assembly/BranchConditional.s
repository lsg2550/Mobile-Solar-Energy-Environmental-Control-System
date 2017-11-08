.global main

/*CMP R1, R2 (SUBTRACTING R2 BY R1)*/
/*If R2 is greater than R1 negative flag is set, otherwise its not*/

main:
MOV R1, #5
MOV R2, #10

CMP R1, R2
BEQ isEqual @Branch(jump) if equal
BGT isGreater @Branch(jump) if greater
B isLess

isEqual:
MOV R0, #500
B end

isGreater:
MOV R0, #0
B end

isLess:
MOV R0, #5

end:
MOV R7, #1
SWI 0
