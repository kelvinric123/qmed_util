#!/usr/bin/env python3
"""
RTM Stream URL Extractor
Extracts the direct HLS stream URL from RTM Klik website
"""

import sys
import subprocess
import json
import os

RTM_URL = "https://rtmklik.rtm.gov.my/live/tv2"
CACHE_FILE = "stream-url.txt"

def check_ytdlp():
    """Check if yt-dlp is installed"""
    # Try as module first (works even if not in PATH)
    try:
        result = subprocess.run([sys.executable, '-m', 'yt_dlp', '--version'], 
                              capture_output=True, 
                              text=True,
                              timeout=5)
        version = result.stdout.strip()
        print(f"[OK] yt-dlp found (version: {version})")
        return 'module'
    except:
        pass
    
    # Try as command
    try:
        result = subprocess.run(['yt-dlp', '--version'], 
                              capture_output=True, 
                              text=True,
                              timeout=5)
        version = result.stdout.strip()
        print(f"[OK] yt-dlp found (version: {version})")
        return 'command'
    except FileNotFoundError:
        print("[ERROR] yt-dlp not found")
        print("\nInstall with:")
        print("  pip install yt-dlp")
        print("  or")
        print("  pip3 install yt-dlp")
        return False
    except Exception as e:
        print(f"[ERROR] Error checking yt-dlp: {e}")
        return False

def extract_stream_url(ytdlp_mode='module'):
    """Extract the direct stream URL using yt-dlp"""
    print(f"\n[EXTRACTING] Stream URL from: {RTM_URL}")
    print("[WAIT] This may take 10-30 seconds...\n")
    
    try:
        # Build command based on how yt-dlp is available
        if ytdlp_mode == 'module':
            cmd = [sys.executable, '-m', 'yt_dlp', '-g', '--no-warnings', RTM_URL]
        else:
            cmd = ['yt-dlp', '-g', '--no-warnings', RTM_URL]
        
        # Run yt-dlp to get the direct URL
        result = subprocess.run(cmd, capture_output=True, text=True, timeout=60)
        
        if result.returncode != 0:
            print(f"[ERROR] yt-dlp failed with error:")
            print(result.stderr)
            return None
        
        # Get the first URL (usually the best quality)
        urls = result.stdout.strip().split('\n')
        
        if not urls or not urls[0]:
            print("[ERROR] No stream URL found")
            return None
        
        stream_url = urls[0].strip()
        
        # Validate URL
        if not stream_url.startswith('http'):
            print(f"[ERROR] Invalid URL format: {stream_url}")
            return None
        
        print(f"[SUCCESS] Stream URL extracted successfully!")
        print(f"\n[URL] Stream URL:")
        print(f"   {stream_url}\n")
        
        return stream_url
        
    except subprocess.TimeoutExpired:
        print("[ERROR] Extraction timed out (took more than 60 seconds)")
        return None
    except Exception as e:
        print(f"[ERROR] Error during extraction: {e}")
        return None

def get_stream_info(ytdlp_mode='module'):
    """Get detailed stream information"""
    print("[INFO] Getting stream information...")
    
    try:
        if ytdlp_mode == 'module':
            cmd = [sys.executable, '-m', 'yt_dlp', '-j', '--no-warnings', RTM_URL]
        else:
            cmd = ['yt-dlp', '-j', '--no-warnings', RTM_URL]
        
        result = subprocess.run(cmd, capture_output=True, text=True, timeout=60)
        
        if result.returncode == 0:
            info = json.loads(result.stdout)
            print(f"\n[DETAILS] Stream Details:")
            print(f"   Title: {info.get('title', 'N/A')}")
            print(f"   Format: {info.get('ext', 'N/A')}")
            print(f"   Resolution: {info.get('resolution', 'N/A')}")
            
            formats = info.get('formats', [])
            if formats:
                print(f"   Available formats: {len(formats)}")
            
            return info
    except Exception as e:
        print(f"[WARNING] Could not get detailed info: {e}")
    
    return None

def save_url_to_cache(url):
    """Save the URL to a cache file"""
    try:
        script_dir = os.path.dirname(os.path.abspath(__file__))
        cache_path = os.path.join(script_dir, CACHE_FILE)
        
        with open(cache_path, 'w') as f:
            f.write(url)
        
        print(f"[SAVED] URL saved to: {cache_path}")
        return True
    except Exception as e:
        print(f"[WARNING] Could not save URL to cache: {e}")
        return False

def load_url_from_cache():
    """Load URL from cache file"""
    try:
        script_dir = os.path.dirname(os.path.abspath(__file__))
        cache_path = os.path.join(script_dir, CACHE_FILE)
        
        if os.path.exists(cache_path):
            with open(cache_path, 'r') as f:
                url = f.read().strip()
            
            if url and url.startswith('http'):
                print(f"[CACHE] Cached URL found: {cache_path}")
                return url
    except Exception as e:
        pass
    
    return None

def main():
    print("=" * 60)
    print("RTM TV2 Stream URL Extractor")
    print("=" * 60)
    
    # Check if --cached flag is used
    use_cache = '--cached' in sys.argv or '-c' in sys.argv
    
    if use_cache:
        cached_url = load_url_from_cache()
        if cached_url:
            print(f"[OK] Using cached URL:\n   {cached_url}\n")
            return cached_url
        else:
            print("[WARNING] No cached URL found, extracting fresh URL...\n")
    
    # Check if yt-dlp is installed
    ytdlp_mode = check_ytdlp()
    if not ytdlp_mode:
        sys.exit(1)
    
    # Extract the stream URL
    stream_url = extract_stream_url(ytdlp_mode)
    
    if not stream_url:
        print("\n[FAILED] Failed to extract stream URL")
        print("\nTroubleshooting:")
        print("  1. Check your internet connection")
        print("  2. Update yt-dlp: pip install --upgrade yt-dlp")
        print("  3. Try manually: yt-dlp -g " + RTM_URL)
        sys.exit(1)
    
    # Save to cache
    save_url_to_cache(stream_url)
    
    # Get additional info
    if '--info' in sys.argv or '-i' in sys.argv:
        get_stream_info(ytdlp_mode)
    
    print("\n" + "=" * 60)
    print("[SUCCESS] EXTRACTION COMPLETE!")
    print("=" * 60)
    print("\nNext steps:")
    print("  1. Copy the URL above")
    print("  2. Use it in stream-player-hls.html")
    print("  3. Or update play-stream.sh (line 55)")
    print("\nOn Raspberry Pi:")
    print("  omxplayer --live '" + stream_url + "'")
    print("\nWith VLC:")
    print("  vlc '" + stream_url + "'")
    print()
    
    return stream_url

if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        print("\n\n[CANCELLED] Extraction cancelled by user")
        sys.exit(1)

