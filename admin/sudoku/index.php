<?php
class SudokuSolver {
    private $board;

    public function __construct(array $board) {
        $this->board = $board;
    }

    public function solve() {
        if ($this->solveSudoku()) {
            return $this->board;
        }
        return false;
    }

    private function solveSudoku() {
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                if ($this->board[$row][$col] == 0) {
                    for ($num = 1; $num <= 9; $num++) {
                        if ($this->isSafe($row, $col, $num)) {
                            $this->board[$row][$col] = $num;
                            if ($this->solveSudoku()) {
                                return true;
                            }
                            $this->board[$row][$col] = 0;
                        }
                    }
                    return false;
                }
            }
        }
        return true;
    }

    private function isSafe($row, $col, $num) {
        // Verificar fila
        for ($x = 0; $x < 9; $x++) {
            if ($this->board[$row][$x] == $num) {
                return false;
            }
        }

        // Verificar columna
        for ($x = 0; $x < 9; $x++) {
            if ($this->board[$x][$col] == $num) {
                return false;
            }
        }

        // Verificar subcuadro 3x3
        $startRow = $row - $row % 3;
        $startCol = $col - $col % 3;
        for ($i = 0; $i < 3; $i++) {
            for ($j = 0; $j < 3; $j++) {
                if ($this->board[$startRow + $i][$startCol + $j] == $num) {
                    return false;
                }
            }
        }

        return true;
    }
}

// Ejemplo de uso
$board = [
    [5, 3, 0, 0, 7, 0, 0, 0, 0],
    [6, 0, 0, 1, 9, 5, 0, 0, 0],
    [0, 9, 8, 0, 0, 0, 0, 6, 0],
    [8, 0, 0, 0, 6, 0, 0, 0, 3],
    [4, 0, 0, 8, 0, 3, 0, 0, 1],
    [7, 0, 0, 0, 2, 0, 0, 0, 6],
    [0, 6, 0, 0, 0, 0, 2, 8, 0],
    [0, 0, 0, 4, 1, 9, 0, 0, 5],
    [0, 0, 0, 0, 8, 0, 0, 7, 9]
];

$solver = new SudokuSolver($board);
$solution = $solver->solve();

if ($solution) {
    foreach ($solution as $row) {
        echo implode(' ', $row) . "\n";
    }
} else {
    echo "No se encontró solución.";
}
?>
