.text
.global main

main:
MOV R0, #65
MOV R1, #232
MOV R7, #1

ADD R3, R1, R0

SWI 0

