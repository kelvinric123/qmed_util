#!/usr/bin/env python3
"""
Smart RTM TV2 URL Extractor
Uses multiple methods to extract the stream URL
"""

import sys
import re
import urllib.request
import urllib.error
import json
import subprocess
from pathlib import Path

def print_header():
    print("=" * 50)
    print("RTM TV2 Smart URL Extractor")
    print("=" * 50)
    print()

def method_1_direct_scrape():
    """Method 1: Direct page scraping"""
    print("Method 1: Direct page scraping...")
    
    url = "https://rtmklik.rtm.gov.my/live/tv2"
    
    try:
        headers = {
            'User-Agent': 'Mozilla/5.0 (X11; Linux armv7l) AppleWebKit/537.36 (KHTML, like Gecko) Chromium/92.0.4515.98 Chrome/92.0.4515.98 Safari/537.36'
        }
        
        req = urllib.request.Request(url, headers=headers)
        with urllib.request.urlopen(req, timeout=10) as response:
            html = response.read().decode('utf-8')
        
        # Look for m3u8 URLs
        m3u8_pattern = r'https?://[^"\'>\s]+\.m3u8[^"\'>\s]*'
        matches = re.findall(m3u8_pattern, html)
        
        if matches:
            # Prefer chunklist over master
            for match in matches:
                if 'chunklist' in match:
                    print(f"  ✅ Found URL: {match[:60]}...")
                    return match
            
            # Return first match if no chunklist found
            print(f"  ✅ Found URL: {matches[0][:60]}...")
            return matches[0]
        
        print("  ❌ No m3u8 URLs found in page")
        return None
        
    except Exception as e:
        print(f"  ❌ Error: {e}")
        return None

def method_2_api_endpoints():
    """Method 2: Try common API endpoints"""
    print("Method 2: Trying API endpoints...")
    
    endpoints = [
        "https://rtmklik.rtm.gov.my/api/v1/live/tv2",
        "https://rtmklik.rtm.gov.my/api/live/tv2",
        "https://myklikapi.rtm.gov.my/api/v1/live/tv2",
    ]
    
    for endpoint in endpoints:
        try:
            req = urllib.request.Request(endpoint)
            with urllib.request.urlopen(req, timeout=5) as response:
                data = json.loads(response.read().decode('utf-8'))
                
                # Try to find stream URL in JSON
                def find_m3u8_in_json(obj):
                    if isinstance(obj, dict):
                        for key, value in obj.items():
                            if isinstance(value, str) and '.m3u8' in value:
                                return value
                            result = find_m3u8_in_json(value)
                            if result:
                                return result
                    elif isinstance(obj, list):
                        for item in obj:
                            result = find_m3u8_in_json(item)
                            if result:
                                return result
                    return None
                
                stream_url = find_m3u8_in_json(data)
                if stream_url:
                    print(f"  ✅ Found URL via API: {stream_url[:60]}...")
                    return stream_url
                    
        except Exception as e:
            continue
    
    print("  ❌ No working API endpoints found")
    return None

def method_3_curl_with_javascript():
    """Method 3: Try to extract from JavaScript variables"""
    print("Method 3: Extracting from JavaScript...")
    
    url = "https://rtmklik.rtm.gov.my/live/tv2"
    
    try:
        result = subprocess.run(
            ['curl', '-s', '-L', url],
            capture_output=True,
            text=True,
            timeout=10
        )
        
        html = result.stdout
        
        # Look for common JavaScript variable patterns
        patterns = [
            r'streamUrl["\']?\s*[:=]\s*["\']([^"\']+\.m3u8[^"\']*)',
            r'source["\']?\s*[:=]\s*["\']([^"\']+\.m3u8[^"\']*)',
            r'src["\']?\s*[:=]\s*["\']([^"\']+\.m3u8[^"\']*)',
            r'videoSrc["\']?\s*[:=]\s*["\']([^"\']+\.m3u8[^"\']*)',
            r'https?://[^\s"\'<>]+\.m3u8[^\s"\'<>]*',
        ]
        
        for pattern in patterns:
            matches = re.findall(pattern, html)
            if matches:
                match = matches[0] if isinstance(matches[0], str) else matches[0][0]
                print(f"  ✅ Found URL in JS: {match[:60]}...")
                return match
        
        print("  ❌ No URLs found in JavaScript")
        return None
        
    except Exception as e:
        print(f"  ❌ Error: {e}")
        return None

def method_4_youtube_dl():
    """Method 4: Try youtube-dl as fallback"""
    print("Method 4: Trying youtube-dl...")
    
    try:
        result = subprocess.run(
            ['youtube-dl', '-g', 'https://rtmklik.rtm.gov.my/live/tv2'],
            capture_output=True,
            text=True,
            timeout=30
        )
        
        if result.returncode == 0 and result.stdout.strip():
            url = result.stdout.strip().split('\n')[0]
            print(f"  ✅ Found URL via youtube-dl: {url[:60]}...")
            return url
        
        print("  ❌ youtube-dl failed")
        return None
        
    except FileNotFoundError:
        print("  ⚠️ youtube-dl not installed")
        return None
    except Exception as e:
        print(f"  ❌ Error: {e}")
        return None

def method_5_known_patterns():
    """Method 5: Try known URL patterns with different parameters"""
    print("Method 5: Trying known URL patterns...")
    
    # Common RTM CDN patterns
    patterns = [
        "https://d25tgymtnqzu8s.cloudfront.net/smil:tv2/playlist.m3u8",
        "https://d25tgymtnqzu8s.cloudfront.net/smil:tv2/chunklist_b2596000_slENG.m3u8?id=2",
        "https://rtm2mobile.secureswiftcontent.com/Origin02/ngrp:RTM2/playlist.m3u8",
    ]
    
    for pattern in patterns:
        try:
            req = urllib.request.Request(pattern)
            req.add_header('User-Agent', 'Mozilla/5.0')
            
            with urllib.request.urlopen(req, timeout=5) as response:
                if response.status == 200:
                    print(f"  ✅ Working URL: {pattern[:60]}...")
                    return pattern
        except:
            continue
    
    print("  ❌ No known patterns working")
    return None

def save_url(url):
    """Save URL to cache file"""
    script_dir = Path(__file__).parent
    cache_file = script_dir / 'www' / 'dev' / 'tv2-stream-url.txt'
    
    cache_file.parent.mkdir(parents=True, exist_ok=True)
    cache_file.write_text(url)
    
    print()
    print(f"✓ URL saved to: {cache_file}")
    print()

def main():
    print_header()
    
    methods = [
        method_1_direct_scrape,
        method_2_api_endpoints,
        method_3_curl_with_javascript,
        method_4_youtube_dl,
        method_5_known_patterns,
    ]
    
    for method in methods:
        try:
            url = method()
            if url:
                print()
                print("=" * 50)
                print("✅ Success!")
                print("=" * 50)
                print()
                print(f"Stream URL: {url}")
                save_url(url)
                
                print("You can now test the player:")
                print("  chromium-browser test-player-simple.html")
                print()
                return 0
        except Exception as e:
            print(f"  ❌ Method failed: {e}")
        
        print()
    
    print("=" * 50)
    print("❌ All methods failed")
    print("=" * 50)
    print()
    print("Please try manual extraction:")
    print("1. Open: https://rtmklik.rtm.gov.my/live/tv2")
    print("2. Press F12 → Network tab → Filter 'm3u8'")
    print("3. Play video and copy the .m3u8 URL")
    print()
    print("See: extract-url-manual-guide.md")
    print()
    
    return 1

if __name__ == '__main__':
    sys.exit(main())

