.global main

main:
MOV R0, #0x14
B other @Branch(jump) to 'other:'
MOV R0, #0xB

other:
MOV R7, #1
SWI 0
