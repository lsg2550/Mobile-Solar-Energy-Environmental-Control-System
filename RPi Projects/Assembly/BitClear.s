.global main

main:
MOV R1, #5
MOV R2, #9
BIC R0, R1, R2

end:
MOV R7, #1
SWI 0
