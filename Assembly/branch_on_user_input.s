.data
firstInput: .asciz "\nPlease input first digit:\n"
secondInput: .asciz "\nPlease input second digit:\n"
equal: .asciz "\nBoth digits are equal.\n"
notEqual: .asciz "\nBoth digits are not equal.\n"
message: .asciz " "

.global main
.extern printf

main:
BL enableOutput
LDR R0, =firstInput
BL printf

BL enableInput
LDR R5, =R0

BL enableOutput
LDR R0, =secondInput
BL printf

BL enableInput
LDR R6, =R0

BL enableOutput
CMP R5, R6
BEQ ifEqual
BNE ifNotEqual
B end

enableOutput:
MOV R7, #4 @Output string
MOV R0, #1 @To monitor
MOV R2, #5 @Write Characters
MOV PC, LR

enableInput:
MOV R7, #3 @Allow keyboard input
MOV R0, #0 @Read from keyboard
MOV R2, #2 @Read 2 characters
LDR R0, =message
MOV PC, LR

ifEqual:
LDR R0, =equal
BL printf
B end

ifNotEqual:
LDR R0, =notEqual
BL printf
B end

end:
MOV R7, #1
SWI 0
