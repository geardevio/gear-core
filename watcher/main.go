package main

import (
	"github.com/fsnotify/fsnotify"
	"log"
	"os"
	"os/exec"
	"path/filepath"
	"strings"
)

func main() {
	// Create new watcher.
	watcher, err := fsnotify.NewWatcher()
	if err != nil {
		log.Fatal(err)
	}
	defer watcher.Close()

	// Start listening for events.
	go func() {
		cmd := exec.Command("php", "/var/www/php/vendor/bin/gear-server.php")
		cmd.Stdout = os.Stdout
		cmd.Stderr = os.Stderr
		err := cmd.Start()
		if err != nil {
			log.Fatal(err)
		}

		for {
			select {
			case event, ok := <-watcher.Events:
				if !ok {
					return
				}
				log.Println("event:", event)
				if event.Has(fsnotify.Write) {
					log.Println("modified file:", event.Name)
					err := cmd.Process.Kill()
					if err != nil {
						log.Fatal(err)
					}
					cmd = exec.Command("php", "/var/www/php/vendor/bin/gear-server.php")
					cmd.Stdout = os.Stdout
					cmd.Stderr = os.Stderr
					err = cmd.Start()
					if err != nil {
						log.Fatal(err)
					}
				}
			case err, ok := <-watcher.Errors:
				if !ok {
					return
				}
				log.Println("error:", err)
			}
		}
	}()

	// Add a path.
	pathsString := strings.Trim(os.Getenv("WATCHER_RELOAD_PATHS"), " ")
	if pathsString == "" {
		pathsString = "app,bootstrap,config,database,public,resources,routes,.env,composer.lock"
	}
	paths := strings.Split(pathsString, ",")
	for _, path := range paths {
		for _, dir := range findAllDirsInADirRecursively(path) {
			err := watcher.Add(dir)
			log.Println("watching", dir)
			if err != nil {
				return
			}
		}
	}

	// Block main goroutine forever.
	<-make(chan struct{})
}

func findAllDirsInADirRecursively(dir string) []string {
	var dirs []string
	err := filepath.Walk(dir, func(path string, info os.FileInfo, err error) error {
		dirs = append(dirs, path)
		return nil
	})
	if err != nil {
		log.Fatal(err)
	}
	return dirs
}
