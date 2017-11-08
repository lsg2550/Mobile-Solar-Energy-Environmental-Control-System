.global main

main:
MOV R7, #4 @System call 4 means we want to output string
MOV R0, #1 @Output stream is monitor
MOV R2, #12 @Length of String
LDR R1, =message
SWI 0

end:
MOV R7, #1
SWI 0

.data
message: .asciz "\nHello World!\n"
