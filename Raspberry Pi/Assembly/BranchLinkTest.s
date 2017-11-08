.global main

end:
MOV R7, #1 //Selects system call for exiting to terminal
SWI 0 //Executes system call via intterupt

subtract: 
SUB R0, #20
MOV PC,LR

main:
MOV R0, #50
BL subtract
B end

