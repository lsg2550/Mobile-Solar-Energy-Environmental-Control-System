.data
firstInput: .asciz "\nPlease input first digit:\n"
secondInput: .asciz "\nPlease input second digit:\n"
equal: .asciz "\nBoth digits are equal.\n"
notEqual: .asciz "\nBoth digits are not equal.\n"
message: .asciz " "

.global main

main:
PUSH {ip,lr}
BL enableOutput
LDR R1, =firstInput
BL enableInput
LDR R1, =message
MOV R5, R2
BL enableOutput
LDR R1, =secondInput
BL enableInput
LDR R1, =message
MOV R6, R2
BL enableOutput
CMP R5, R6
BEQ ifEqual
BL ifNotEqual
POP {ip, pc}
B end

enableOutput:
PUSH {R4, lr}
MOV R7, #4 @Output string
MOV R0, #1 @To monitor
MOV R2, #5 @Write Characters
POP {R4, pc}

enableInput: @Enable input for 2 characters
PUSH {R4, lr}
MOV R7, #3 @Allow keyboard input
MOV R0, #0 @Read from keyboard
MOV R2, #2 @Read 2 characters
POP {R4, pc}

ifEqual:
PUSH {R4, lr}
LDR R1, =equal
POP {R4, pc}

ifNotEqual:
PUSH {R4, lr}
LDR R1, =notEqual
POP {R4, pc}

end:
MOV R7, #1
SWI 0
