import subprocess
import time
import os
import re
import glob
from listener import amanda_listen

def clean_text_for_ai(text):
    text = text.replace(r'\(', '').replace(r'\)', '')
    text = text.replace('\\', '')
    text = text.replace(r'^{\circ}', ' graus ').replace("'", " minutos ").replace('"', ' segundos ')
    text = re.sub(r'\s+', ' ', text)
    return text.strip()

def get_model_path():
    new_folder = "/home/brenoads/amanda_ia/core/voz_dii"
    onnx_files = glob.glob(os.path.join(new_folder, "*.onnx"))
    
    if onnx_files:
        return onnx_files[0]
    return "/home/brenoads/amanda_ia/core/voz_feminina.onnx"

def speak(text):
    print(f"Amanda diz: {text}")
    
    cleaned_text = clean_text_for_ai(text)
    audio_file = "/tmp/amanda_fala.wav"
    model_path = get_model_path()
    
    try:
        subprocess.run(
            ['piper', '--model', model_path, '--output_file', audio_file, '--length_scale', '1.15'],
            input=cleaned_text,
            text=True,
            capture_output=True
        )
        
        os.system(f"play -q {audio_file} > /dev/null 2>&1")
        
        if os.path.exists(audio_file):
            os.remove(audio_file)
            
    except Exception as e:
        print(f"[X] Internal error during binary execution: {e}")

def main():
    print("Starting Amanda Core (15s Session + 30s Gratitude Window)...")
    
    model_path = get_model_path()
    if not os.path.exists(model_path):
        print(f"[!] Critical Error: No .onnx model found for Piper TTS.")
        exit(1)
        
    last_interaction = 0 
    amanda_triggers = ["amanda", "manda", "armando", "amada", "ama"]
    
    while True:
        heard_text = amanda_listen()
        
        if heard_text:
            lower_text = heard_text.lower().strip()
            now = time.time()
            
            has_wake_word = any(trigger in lower_text for trigger in amanda_triggers)
            window_15s_open = (now - last_interaction) <= 15
            window_30s_open = (now - last_interaction) <= 30
            is_gratitude = "obrigado" in lower_text or "obrigada" in lower_text
            
            # RULE 1: Gratitude within 30 seconds of the last interaction
            if is_gratitude and window_30s_open:
                print(f"[!] Gratitude detected: {heard_text}")
                speak("Por nada!")
                last_interaction = 0 # Resets the timer, ending the conversation
                continue
                
            # RULE 2: Block. If AI wasn't called and 15s window is closed, ignore speech
            if not has_wake_word and not window_15s_open:
                continue
                
            # RULE 3: Direct trigger (Called only by name)
            if lower_text in amanda_triggers:
                print(f"[!] Direct trigger activated: {heard_text}")
                speak("Oi, o que voce esta a precisar?")
                last_interaction = time.time() # Starts the 15 seconds
                continue 
                
            # RULE 4: Send to PHP (Phrase has wake word or window is open)
            print(f"[!] Sending intent to PHP: {heard_text}")
            
            try:
                result = subprocess.run(
                    ['php', '/home/brenoads/amanda_ia/core/brain.php', heard_text],
                    capture_output=True,
                    text=True,
                    check=True
                )
                
                amanda_response = result.stdout.strip()
                
                if amanda_response:
                    speak(amanda_response)
                    last_interaction = time.time() # Renews the 15 seconds for the next question
                    
            except subprocess.CalledProcessError as e:
                error_msg = f"[X] Communication error with PHP core. Details: {e}"
                print(error_msg)
                speak("Houve uma falha no servidor PHP.")

if __name__ == "__main__":
    main()