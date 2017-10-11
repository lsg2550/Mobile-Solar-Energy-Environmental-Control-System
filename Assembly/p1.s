@This is the data segment
.data
msg1:	.asciz "\nFirst ASM Program running\n"
msg2:	.asciz "\non 64-bit Quad Core ARM Cortex A53 with \n"
msg3:	.asciz "\n8-stage pipeline. It has L1 64K I-Cache and L1 64k D-Cache.\n"
msg4:	.asciz "\nIt has 128KB of L2 cache with SCU (Snoop Control Unit) and ACP (Accelerator Coherency Port)\n"

@This is the code segment
.text
.global main
.extern printf

main:
push {ip,lr} @Save the return address in the link register

@Load and Print Messages
ldr r0, =msg1
bl printf
ldr r0, =msg2
bl printf
ldr r0, =msg3
bl printf
ldr r0, =msg4
bl printf

pop {ip,pc} @Restore the return address into Program Counter
