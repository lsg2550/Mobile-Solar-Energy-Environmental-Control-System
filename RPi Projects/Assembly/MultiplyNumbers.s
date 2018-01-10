.global main

main:
MOV R1, #0x14
MOV R2, #0xA
MUL R0, R1, R2

@These are used for ending the program, apparently.
MOV R7, #1
SWI 0
