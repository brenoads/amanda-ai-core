import sys
import queue
import json
import sounddevice as sd
from vosk import Model, KaldiRecognizer

# Queue to store real-time audio packets from the sound card
audio_queue = queue.Queue()

def callback(indata, frames, time, status):
    """Pushes microphone bytes into the Vosk processing queue"""
    if status:
        print(status, file=sys.stderr)
    audio_queue.put(bytes(indata))

print("[*] Loading offline listening engine (Vosk PT-BR)...")
model_path = "/home/brenoads/amanda_ia/core/vosk_model"

try:
    # Loads the listening model into global RAM
    vosk_model = Model(model_path)
except Exception as e:
    print(f"[X] Critical Error: Vosk model not found at {model_path}")
    sys.exit(1)

def amanda_listen():
    """Captures and transcribes real-time audio with zero latency (Offline Edge Computing)"""
    print("\n==================================================")
    print("Amanda: Estou ouvindo, Breno. Pode falar!")
    
    # Opens raw audio stream from the microphone (16kHz, Mono)
    with sd.RawInputStream(samplerate=16000, blocksize=8000, device=None, 
                           dtype='int16', channels=1, callback=callback):
        
        recognizer = KaldiRecognizer(vosk_model, 16000)
        
        while True:
            data = audio_queue.get()
            
            # If Vosk detects the end of a spoken phrase
            if recognizer.AcceptWaveform(data):
                result_json = json.loads(recognizer.Result())
                transcribed_text = result_json.get("text", "")
                
                if transcribed_text:
                    print(f"[ VOCÊ DISSE ]: '{transcribed_text}'")
                    return transcribed_text