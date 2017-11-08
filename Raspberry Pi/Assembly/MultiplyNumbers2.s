.global main

main:
MOV R1, #0x14
MOV R2, #0xA
MOV R3, #0x5

@Multiply w/ Accummulate
MLA R0, R1, R2, R3

@Exit
MOV R7, #1
SWI 0
