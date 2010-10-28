;; lib.scm : library of miscellaneous functions used throughout the project
(load "lang.scm")

(define MAX-IDX 0)

(define constr-var
  (lambda (L n)
    (string->symbol (string-append L (number->string n)))))

(define get-fresh-symbol
  (lambda ()
    (begin (set! MAX-IDX (+ 1 MAX-IDX))
           (constr-var "Z" MAX-IDX))))

(define get-fresh-label
  (lambda ()
    (begin (set! MAX-IDX (+ 1 MAX-IDX))
           (constr-var "A" MAX-IDX))))
  
; label-first: labels the first in a list of unlabeled instructions
(define label-first
  (lambda (l list)
    (cases instruction (car list)
      (unlabeled (i) (cons (labeled l i)
                           (cdr list)))
      (else (eopl:error "label-first: expected list of unlabeled")))))

(define implode
  (lambda (list)
    (cond
      ((null? list) "")
      ((symbol? (car list)) (string-append (symbol->string (car list)) " " (implode (cdr list))))
      ((number? (car list)) (string-append (number->string (car list)) " " (implode (cdr list))))      
      (else "?"))))

; max-index - finds the max used index in a program
(define max-index
  (lambda (prog)
    (cases program prog
      (a-program (P) (max-index-instructions P)))))

(define max-index-instructions
  (lambda (list)
    (cond
      ((null? list) 0)
      (else (max (max-index-instruction (car list))
                 (max-index-instructions (cdr list)))))))

(define max-index-instruction
  (lambda (instr)
    (cases instruction instr
      (labeled (l i) (max (get-index l) (max-index-statement i)))
      (unlabeled (i) (max-index-statement i)))))

(define max-index-statement
  (lambda (sta)
    (cases statement sta
      (add1 (V) (get-index V))
      (sub1 (V) (get-index V))
      (skip (V) (get-index V))
      (zero-V (V) (get-index V))
      (assign (V1 V2) (max (get-index V1) (get-index V2)))
      (if-goto (V l) (max (get-index V) (get-index l)))
      (goto-L (l) (get-index l))
      (mac-call (V m args) (get-index V)))))

(define get-index
  (lambda (symb)
    (or (string->number (substring (symbol->string symb) 1)) 0)))

(define value
  (lambda (symb s)
    (cases state s
      (empty-state () 0)
      (extend-state (V n s) (if (eq? V symb) n (value symb s))))))

; pretty-print

(define pretty-print
  (lambda (prog)
    (cases program prog
      (a-program (P) (pretty-print-instructions P)))))

(define pretty-print-instructions
  (lambda (list)
    (cond
      ((null? list) (newline))
      (else (pretty-print-instruction (car list))
            (pretty-print-instructions (cdr list))))))

(define pretty-print-instruction
  (lambda (instr)
    (cases instruction instr
      (labeled (l i) (display (string-append "<" (symbol->string l) "> " (pretty-print-statement i) "\n")))
      (unlabeled (i) (display (string-append "     " (pretty-print-statement i) "\n")))
      )))

(define pretty-print-statement
  (lambda (sta)
    (cases statement sta
      (add1 (V) (string-append (symbol->string V) " <- " (symbol->string V) " + 1"))
      (sub1 (V) (string-append (symbol->string V) " <- " (symbol->string V) " - 1"))
      (skip (V) (string-append (symbol->string V) " <- " (symbol->string V)))
      (zero-V (V) (string-append (symbol->string V) " <- 0"))
      (assign (V1 V2) (string-append (symbol->string V1) " <- " (symbol->string V2)))
      (if-goto (V l) (string-append "IF " (symbol->string V) " =/= 0 GOTO " (symbol->string l)))
      (goto-L (l) (string-append "GOTO "(symbol->string l)))
      (mac-call (V m args) (string-append (symbol->string V) " <- " (symbol->string m) "(" (implode args) ")"))
      (else "Unknown statement")
      )))

; parse superlist of symbols

(define parse
  (lambda (p)
    (a-program (parse-instructions p))))

(define parse-instructions
  (lambda (i)
    (cond
      ((null? i) '())
      ((equal? (substring (symbol->string (car i)) 0 1) "<")
       (let ((label-str (symbol->string (car i)))
             (state (cdr i)))
         (let ((label (string->symbol (substring label-str 1 (- (string-length label-str) 1))))
               (state-parse (parse-statement (cdr i))))
           (if (and (statement? (car state-parse))
                    (symbol? label))
               (cons (labeled label
                              (car state-parse))
                     (parse-instructions (cdr state-parse)))
               (eopl:error "invalid concrete syntax")))))
      (else (let ((ret (parse-statement i)))
              (cons (unlabeled (car ret)) 
                    (parse-instructions (cdr ret))))))))

;returns (#struct (remaining tokens))
(define parse-statement
  (lambda (s)
    (if (list? s)
        (cond
          ((if-goto? s) (cons (if-goto (cadr s) (caddr (cdddr s))) (cdddr (cdddr s))));6
          ((add1? s) (cons (add1 (car s)) (cdddr (cddr s))));5
          ((sub1? s) (cons (sub1 (car s)) (cdddr (cddr s))));5
          ((mac-call? s) (cons (mac-call (car s) (caddr s) (cadddr s)) (cddddr s)));4
          ((skip? s) (cons (skip (car s)) (cdddr s)));3
          ((zero-V? s) (cons (zero-V (car s)) (cdddr s)));3
          ((assign? s) (cons (assign (car s) (caddr s)) (cdddr s)));3
          ((goto-L? s) (cons (goto-L (cadr s)) (cddr s)));2
          (else (eopl:error "bad statement")))
        (eopl:error "bad statement"))))

(define prep-args
  (lambda (l)
    (cond 
      ((null? l) 1)
      ((= 1 (prep-args (cdr l))) '())
      (else (cons (car l) (prep-args (cdr l)))))))
        
(define add1?
  (lambda (s)
    (if (>= (length s) 5)
        (let ((arg1 (car s))
              (arg2 (cadr s))
              (arg3 (caddr s))
              (arg4 (cadddr s))
              (arg5 (cadddr (cdr s))))
          (and (symbol? arg1)
               (symbol? arg3)
               (eqv? arg1 arg3)
               (eqv? arg2 '<-)
               (eqv? arg4 '+)
               (eqv? arg5 1)))
        #f)))

(define sub1?
  (lambda (s)
    (if (>= (length s) 5)
        (let ((arg1 (car s))
              (arg2 (cadr s))
              (arg3 (caddr s))
              (arg4 (cadddr s))
              (arg5 (cadddr (cdr s))))
          (and (symbol? arg1)
               (symbol? arg3)
               (eqv? arg1 arg3)
               (eqv? arg2 '<-)
               (eqv? arg4 '-)
               (eqv? arg5 1)))
        #f)))

(define skip?
  (lambda (s)
    (if (>= (length s) 3)
        (let ((arg1 (car s))
              (arg2 (cadr s))
              (arg3 (caddr s)))
          (and (symbol? arg1)
               (symbol? arg3)
               (eqv? arg1 arg3)
               (eqv? arg2 '<-)))
        #f)))

(define zero-V?
  (lambda (s)
    (if (>= (length s) 3)
        (let ((arg1 (car s))
              (arg2 (cadr s))
              (arg3 (caddr s)))
          (and (symbol? arg1)
               (eqv? arg2 '<-)
               (eqv? arg3 0)))
        #f)))

(define assign?
  (lambda (s)
    (if (>= (length s) 3)
        (let ((arg1 (car s))
              (arg2 (cadr s))
              (arg3 (caddr s)))
          (and (symbol? arg1)
               (symbol? arg3)
               (eqv? arg2 '<-)))
        #f)))

(define if-goto?
  (lambda (s)
    (if (>= (length s) 6)
        (let ((arg1 (car s))
              (arg2 (cadr s))
              (arg3 (caddr s))
              (arg4 (cadddr s))
              (arg5 (cadddr (cdr s)))
              (arg6 (cadddr (cddr s))))
          (and (symbol? arg2)
               (symbol? arg6)
               (eqv? arg1 'IF)
               (eqv? arg3 '=/=)
               (eqv? arg4 '0)
               (eqv? arg5 'GOTO)))
        #f)))

(define goto-L?
  (lambda (s)
    (if (>= (length s) 2)
        (let ((arg1 (car s))
              (arg2 (cadr s)))
          (and (symbol? arg2)
               (eqv? arg1 'GOTO)))
        #f)))

(define mac-call?
  (lambda (s)
    (if (>= (length s) 4)
        (let ((arg1 (car s))
              (arg2 (cadr s))
              (arg3 (caddr s))
              (arg4 (cadddr s)))
          (and (symbol? arg1)
               (symbol? arg3)
               (pair? arg4)
               (eqv? arg2 '<-)))
        #f)))