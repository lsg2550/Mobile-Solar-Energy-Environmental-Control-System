.global main

main:
MOV R7, #3 @Allow keyboard input = 3
MOV R0, #0 @Input stream will be keyboard
MOV R2, #10 @10 Characters
LDR R1, =message
SWI 0

write:
MOV R7, #4
MOV R0, #1
MOV R2, #5
LDR R1, =message
SWI 0

end:
MOV R7, #1
SWI 0

.data
message: .asciz " "
