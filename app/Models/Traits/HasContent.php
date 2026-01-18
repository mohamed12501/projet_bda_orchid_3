<?php

namespace App\Models\Traits;

trait HasContent
{
    /**
     * Get content for Orchid Platform display
     */
    public function getContent(): string
    {
        // Return a string representation of the model
        if (isset($this->nom)) {
            // For models with prenom, combine them
            if (isset($this->prenom)) {
                return trim($this->prenom . ' ' . $this->nom);
            }
            return (string) $this->nom;
        }
        
        if (isset($this->name)) {
            return (string) $this->name;
        }
        
        if (isset($this->email)) {
            return (string) $this->email;
        }
        
        // Fallback to primary key
        $key = $this->getKey();
        if ($key !== null) {
            return (string) $key;
        }
        
        // Last resort: class name
        return class_basename($this);
    }
}
