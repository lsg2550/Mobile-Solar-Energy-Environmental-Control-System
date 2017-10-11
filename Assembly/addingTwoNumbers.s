.text
.global main

/*<instruction> <Dest>, <Operand>, <Operand>*/

main:
MOV R1, #0xA
ADD R0, R1, #0xB

MOV R7, #1
SWI 0

