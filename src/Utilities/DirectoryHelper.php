<?php

class DirectoryHelper {
    public static function searchClassesInFiles($directory) {
        
        $classPattern = '/class\s+(\w+)\s*(extends\s+\w+)?\s*(implements\s+[\w, ]+)?\s*{/';  
        $classesFound = [];  // Array to store found classes
    
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() == 'php') {
                $filePath = $file->getRealPath();
                try {
                    $contents = file_get_contents($filePath);
                    preg_match_all($classPattern, $contents, $matches);
                    if (!empty($matches[1])) {
                        foreach ($matches[1] as $className) {
                            $classesFound[] = ['file' => $filePath, 'class' => $className];
                        }
                    }
                } catch (Exception $e) {
                    error_log("Could not read file $filePath: " . $e->getMessage() . "\n");
                }
            }
        }
    
        return $classesFound;
    }

}