(load "lang.scm")
(load "lib.scm")

;; Instantiate macro ---------------------------------

(define instantiate-macro
  (lambda (mac params)
    (cases macro mac
      (a-macro (name param-list body) 
               (cases parameters param-list
                 (param-lists (out in loc exit label) 
                              (instantiate-macro-params params (list out
                                                                     in
                                                                     loc
                                                                     exit
                                                                     label) body)))))))

(define instantiate-macro-params
  (lambda (new old body)
    (cond
      ((null? old) body)
      ((null? new) (eopl:error "bad number of macro arguments")) ;old is not empty but new is -- bad match
      (((list-of symbol?) (car old)) (if ((list-of symbol?) (car new))
                                         (instantiate-macro-params (car new) (car old) (instantiate-macro-params (cdr new) (cdr old) body)) ;call self with the sub list
                                         (eopl:error "macro arguments do not match")))
      (else (instantiate-macro-params (cdr new) (cdr old) (replace-symbol-body (car old) (car new) body))))))

(define replace-symbol-body
  (lambda (find replace body)
    (cond
      ((null? body) '())
      (else (cases instruction (car body)
              (labeled (l i) (cons (labeled (replace-symbol find replace l) (replace-symbol-statement find replace i))
                                   (replace-symbol-body find replace (cdr body))))
              (unlabeled (i) (cons (unlabeled (replace-symbol-statement find replace i))
                               (replace-symbol-body find replace (cdr body)))))))))

(define replace-symbol-statement 
  (lambda (find replace state)
    (cases statement state
      (add1 (V) (add1 (replace-symbol find replace V)))
      (sub1 (V) (sub1 (replace-symbol find replace V)))
      (skip (V) (skip (replace-symbol find replace V)))
      (zero-V (V) (zero-V (replace-symbol find replace V)))
      (assign (V1 V2) (assign (replace-symbol find replace V1) (replace-symbol find replace V2)))
      (if-goto (V l) (if-goto (replace-symbol find replace V) (replace-symbol find replace l)))
      (goto-L (l) (goto-L (replace-symbol find replace l)))
      (mac-call (V m args) (mac-call (replace-symbol find replace V) m args)))))

(define replace-symbol
  (lambda (find replace symb)
    (if (eq? find symb)
        replace
        symb)))
       
;; GOTO L ---------------------------------

(define expand-GOTOS
  (lambda (prog)
    (begin (set! MAX-IDX (max-index prog))
           (cases program prog
             (a-program (P) (a-program (expand-GOTOS-program P)))))))

(define expand-GOTOS-program
  (lambda (list)
    (cond
      ((null? list) '())
      (else (append (expand-GOTOS-instruction (car list))
                    (expand-GOTOS-program (cdr list)))))))

(define expand-GOTOS-instruction
  (lambda (instr)
    (cases instruction instr
      (labeled (l i) (label-first l (expand-GOTOS-statement i)))
      (unlabeled (i) (expand-GOTOS-statement i)) )))

(define expand-GOTOS-statement
  (lambda (state)
    (cases statement state
      (goto-L (l) (let ((fresh (get-fresh-symbol)))
                    (list (unlabeled (add1 fresh))
                          (unlabeled (if-goto fresh l))) ))
      (else (cons (unlabeled state) '()))))) ;return original - cons to empty list so append works

;; V <- 0 ---------------------------------

(define expand-ZEROV
  (lambda (prog)
    (begin (set! MAX-IDX (max-index prog))
           (cases program prog
             (a-program (P) (a-program (expand-ZEROV-program P)))))))

(define expand-ZEROV-program
  (lambda (list)
    (cond
      ((null? list) '())
      (else (append (expand-ZEROV-instruction (car list))
                    (expand-ZEROV-program (cdr list)))))))

(define expand-ZEROV-instruction
  (lambda (instr)
    (cases instruction instr
      (labeled (l i) (label-first l (expand-ZEROV-statement i)))
      (unlabeled (i) (expand-ZEROV-statement i)))))

(define expand-ZEROV-statement
  (lambda (state)
    (cases statement state
      (zero-V (V) (let ((fresh (get-fresh-label)))
                    (list (unlabeled (skip V))
                          (labeled fresh (sub1 V))
                          (unlabeled (if-goto V fresh)))))
      (else (cons (unlabeled state) '()))))) ;return original - cons to empty list so append works

;; V <- V2 ---------------------------------

(define expand-Assign ; changed from ASSIGN, leaving the rest my way
  (lambda (prog)
    (begin (set! MAX-IDX (max-index prog))
           (cases program prog
             (a-program (P) (a-program (expand-ASSIGN-program P)))))))

(define expand-ASSIGN-program
  (lambda (list)
    (cond
      ((null? list) '())
      (else (append (expand-ASSIGN-instruction (car list))
                    (expand-ASSIGN-program (cdr list)))))))

(define expand-ASSIGN-instruction
  (lambda (instr)
    (cases instruction instr
      (labeled (l i) (label-first l (expand-ASSIGN-statement i)))
      (unlabeled (i) (expand-ASSIGN-statement i)))))

(define expand-ASSIGN-statement
  (lambda (state)
    (cases statement state
      (assign (V1 V2) (let ((L1 (get-fresh-label))
                            (L2 (get-fresh-label))
                            (L3 (get-fresh-label))
                            (V  (get-fresh-symbol)))
                        (append (expand-ZEROV-statement (zero-V V1)) ;        V1 <- 0
                                (list (unlabeled (if-goto V2 L1))
                                      (unlabeled (goto-L L3))          ;      GOTO L3
                                      (labeled L1 (sub1 V2))           ; <L1> V2 <- V2 - 1
                                      (unlabeled (add1 V1))            ;      V1 <- V1 + 1
                                      (unlabeled (add1 V))             ;      V <- V + 1
                                      (unlabeled (if-goto V2 L1))      ;      IF V2 != 0 GOTO L1
                                      (labeled L2 (sub1 V))            ; <L2> V <- V - 1
                                      (unlabeled (add1 V2))            ;      V2 <- V2 + 1
                                      (unlabeled (if-goto V L2))       ;      IF V != 0 GOTO L2
                                      (labeled L3 (skip V))))))        ; <L3> V <- V
      
      (else (cons (unlabeled state) '()))))) ;return original - cons to empty list so append works


;; V <- func (V1 ... Vn) ---------------------------------

(define expand-macro
  (lambda (p list-of-macros)
    (begin (set! MAX-IDX (max-index p))
           (cases program p
             (a-program (list) (a-program (expand-macro-prog list list-of-macros)))))))

(define expand-macro-prog
  (lambda (list list-of-macros)
    (cond
      ((null? list) '())
      (else (append (expand-macro-instr (car list) list-of-macros)
                    (expand-macro-prog (cdr list) list-of-macros))))))

(define expand-macro-instr
  (lambda (instr list-of-macros)
    (cases instruction instr
      (labeled (l i) (label-first l (expand-macro-state i list-of-macros)))
      (unlabeled (i) (expand-macro-state i list-of-macros)))))

; returns a list of instructions
(define expand-macro-state
  (lambda (state list-of-macros)
    (cases statement state
      (mac-call (V m args) (let ((mac (find-macro m list-of-macros)))
                             (expand-macro-body V mac (assign-params mac) args)))
      (else (list (unlabeled state))))))

; since we have a fully instantiated macro at this point, just prepare and return
(define expand-macro-body
  (lambda (V mac fresh args)
    (cases parameters fresh
      (param-lists (out in loc exit label)
                   (append (list (unlabeled (skip V))
                                 (unlabeled (zero-V out)))
                           (expand-macro-inputs in args)
                           (expand-macro-locals loc)
                           (instantiate-macro mac (list out in loc exit label))
                           (list (unlabeled (assign V out))))))))

; returns list of Zn <- ARGn
(define expand-macro-inputs
  (lambda (in args)
    (cond
      ((null? in) '())
      ((null? args) (eopl:error "invalid arg match"))
      (else (cons (unlabeled (assign (car in) (car args)))
                  (expand-macro-inputs (cdr in) (cdr args)))))))

; returns list of Zn <- 0 (locals)
(define expand-macro-locals
  (lambda (loc)
    (cond
      ((null? loc) '())
      (else (cons (unlabeled (zero-V (car loc)))
                  (expand-macro-locals (cdr loc)))))))

; break apart a macro, return a fresh param-list of symbols/labels for instantiation
(define assign-params
  (lambda (mac)
    (cases macro mac
      (a-macro (name params body) 
               (cases parameters params
                 (param-lists (out in loc exit label) 
                              (param-lists (get-fresh-symbol)
                                           (assign-params-list in get-fresh-symbol)
                                           (assign-params-list loc get-fresh-symbol)
                                           (get-fresh-label)
                                           (assign-params-list label get-fresh-label))))))))

(define assign-params-list
  (lambda (list get-func) ;get-func is either get-fresh-symbol or get-fresh-label
    (cond
      ((null? list) '())
      (else (cons (get-func) (assign-params-list (cdr list) get-func))))))
  
(define find-macro
  (lambda (m list)
    (cond
      ((null? list) (eopl:error "could not find macro ~s in list" m))
      (else (cases macro (car list)
              (a-macro (name param body) (if (eq? name m)
                                             (car list)
                                             (find-macro m (cdr list)))))))))

;; clean-up ---------------------------------

(define clean-up
  (lambda (p lom)
    (cond
      ((has-state? p mac-call) (clean-up (expand-macro p lom) lom))
      ((has-state? p goto-L) (clean-up (expand-GOTOS p) lom))
      ((has-state? p zero-V) (clean-up (expand-ZEROV p) lom))
      ((has-state? p assign) (clean-up (expand-Assign p) lom))
      (else p))))

; searches program for statement
(define has-state?
  (lambda (prog state)
    (cases program prog
      (a-program (list) (has-state?-program list state)))))

(define has-state?-program
  (lambda (list state)
    (cond
      ((null? list) #f)
      ((has-state?-instruction (car list) state) #t)
      (else (has-state?-program (cdr list) state)))))

(define has-state?-instruction
  (lambda (instr state)
    (cases instruction instr
      (labeled (l i) (has-state?-statement i state))
      (unlabeled (i) (has-state?-statement i state)))))

(define has-state?-statement
  (lambda (state target)
    (cases statement state
      (mac-call (V m a) (if (eq? target mac-call) #t #f))
      (zero-V (V) (if (eq? target zero-V) #t #f))
      (assign (V1 V2) (if (eq? target assign) #t #f))
      (goto-L (L) (if (eq? target goto-L) #t #f))
      (else #f))))

;; eval-program ---------------------------------

; this does NOT call clean up for you -- you must do it yourself first (with your macro list)
(define eval-program
  (lambda (p s)
    (cases program p
      (a-program (list) (eval-program-results (car (reverse (succ 1 s list))))))))

; instead of printing Y, this prints the entire snapshot history
(define debug-program
  (lambda (p s)
    (cases program p
      (a-program (list) (succ 1 s list)))))

(define eval-program-results
  (lambda (snap)
    (cases snapshot snap
      (a-snapshot (i s) (display (string-append "Y = " (number->string (value 'Y s))))))))

(define succ
  (lambda (i sta instr)
    (cond
      ((> i (length instr)) '())
      (else (cons (a-snapshot i sta) 
                  (cases statement (lookup-instr-n instr i)
                    (add1 (V) (succ (+ i 1) (extend-state V (+ (value V sta) 1) sta) instr))
                    (sub1 (V) (succ (+ i 1) (extend-state V (max (- (value V sta) 1) 0) sta) instr))
                    (skip (V) (succ (+ i 1) sta instr))
                    (if-goto (V l) (if (eq? 0 (value V sta))
                                       (succ (+ i 1) sta instr)
                                       (succ (lookup-instr-lbl instr l) sta instr)))
                    (else (eopl:error "program must be run through clean-up first before evaluating"))))))))

(define lookup-instr-n
  (lambda (body n)
    (cond
      ((null? body) (eopl:error "bad index"))
      ((= n 1) (cases instruction (car body)
                 (labeled (l i) i)
                 (unlabeled (i) i)))
      (else (lookup-instr-n (cdr body) (- n 1))))))

; returns pc number of a given label
(define lookup-instr-lbl
  (lambda (body lbl)
    (cond
      ((null? body) 1)
      (else (cases instruction (car body)
              (labeled (l i) (if (eq? l lbl) 1 (+ 1 (lookup-instr-lbl (cdr body) lbl))))
              (unlabeled (+ 1 (lookup-instr-lbl (cdr body) lbl))))))))
