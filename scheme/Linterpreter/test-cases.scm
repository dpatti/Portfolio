(load "PattiDouglas-hw3.scm")

; see final test cases at the end

;----------------------------------------------------------
; (instantiate-macro ...)
;(define myinstrs-1
;  (list (labeled 'A1 (add1 'X1)) 
;        (unlabeled (zero-V 'Z2))
;        (unlabeled (if-goto 'Z1 'A1))
;        (labeled 'A2 (sub1 'Y))
;        (unlabeled (add1 'X2))
;        (unlabeled (sub1 'X3))))
;
;(define mymacro-1
;  (a-macro 'func
;           (param-lists 'Y '(X1 X2 X3) '(Z1 Z2) 'E '(A1 A2))
;           myinstrs-1))
;
;(define macrolist
;  (list mymacro-1))

;(instantiate-macro mymacro-1 '(Z10 (Z11 Z12 Z13) (Z14 Z15) A10 (A11 A12)))


; for the next 4 ...
;(define myexpand
;  (parse '(<A1> Z1 <- Z1 + 1
;                Z2 <- Z2 - 1
;                Z5 <- func ( Z4 Z6 Z7 )
;                IF Z3 =/= 0 GOTO A2
;           <A2> GOTO A3
;                GOTO A4        
;           <A3> Z4 <- 0
;                Z5 <- 0
;           <A4> Z6 <- Z4
;                Z6 <- Z5)))

;(pretty-print myexpand)

; (expand-GOTOS ...)
;(pretty-print (expand-GOTOS myexpand))

; (expand-ZEROV ...)
;(pretty-print (expand-ZEROV myexpand))

; (expand-Assign ...)
;(pretty-print (expand-Assign myexpand))


; ---------------------------------------------------------------------------------------------
; full test: multiply X1 and X2
(define full-test
  (parse '(     IF X2 =/= 0 GOTO A1  ; second arg is 0
                Y <- 0               ; return 0
                GOTO E
           <A1> Z1 <- add (Z1 X1)
                X2 <- X2 - 1
                IF X2 =/= 0 GOTO A1
                Y <- Z1)))

; macro: add X1 + X2
(define add-n
  (a-macro 'add
           (param-lists 'Y '(X1 X2) '() 'E '(A1))
           (cases program (parse '(     IF X2 =/= 0 GOTO A1
                                        GOTO E
                                   <A1> X1 <- X1 + 1
                                        X2 <- X2 - 1
                                        IF X2 =/= 0 GOTO A1
                                   <E>  Y <- X1))
             (a-program (instrs) instrs))))

(define my-macros
  (list add-n))

(pretty-print full-test)
(pretty-print (expand-macro full-test my-macros))
;(pretty-print (clean-up full-test my-macros))
;(debug-program (clean-up full-test my-macros) (extend-state 'X2 0 (extend-state 'X1 6 (empty-state))))
(eval-program (clean-up full-test my-macros) (extend-state 'X2 7 (extend-state 'X1 6 (empty-state))))

