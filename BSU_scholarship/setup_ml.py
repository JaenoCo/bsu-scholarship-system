#!/usr/bin/env python3
"""
Setup script for ML Analytics
Run this to install dependencies and test the ML system
"""

import subprocess
import sys
import os
import json

def install_requirements():
    """Install Python requirements"""
    print("🔧 Installing Python ML dependencies...")
    try:
        subprocess.check_call([sys.executable, "-m", "pip", "install", "-r", "requirements.txt"])
        print("✅ Dependencies installed successfully!")
        return True
    except subprocess.CalledProcessError as e:
        print(f"❌ Failed to install dependencies: {e}")
        return False

def test_ml_system():
    """Test the ML system with sample data"""
    print("\n🧪 Testing ML Analytics System...")
    
    try:
        from ml_analytics import run_ml_analysis
        
        # Sample data for testing
        sample_data = {
            'applications': [
                {'id': 1, 'user_id': 1, 'scholarship_id': 1, 'status': 'approved', 'created_at': '2025-01-01'},
                {'id': 2, 'user_id': 2, 'scholarship_id': 1, 'status': 'rejected', 'created_at': '2025-01-02'},
                {'id': 3, 'user_id': 3, 'scholarship_id': 2, 'status': 'approved', 'created_at': '2025-01-03'},
                {'id': 4, 'user_id': 4, 'scholarship_id': 2, 'status': 'pending', 'created_at': '2025-01-04'},
                {'id': 5, 'user_id': 5, 'scholarship_id': 1, 'status': 'approved', 'created_at': '2025-01-05'},
            ],
            'students': [
                {'id': 1, 'campus_id': 1, 'campus_type': 'constituent'},
                {'id': 2, 'campus_id': 1, 'campus_type': 'constituent'},
                {'id': 3, 'campus_id': 2, 'campus_type': 'extension'},
                {'id': 4, 'campus_id': 1, 'campus_type': 'constituent'},
                {'id': 5, 'campus_id': 2, 'campus_type': 'extension'},
            ],
            'scholarships': [
                {'id': 1, 'scholarship_type': 'academic', 'grant_type': 'full', 'grant_amount': 50000},
                {'id': 2, 'scholarship_type': 'merit', 'grant_type': 'partial', 'grant_amount': 25000},
            ]
        }
        
        # Run ML analysis
        results = run_ml_analysis(
            sample_data['applications'],
            sample_data['students'],
            sample_data['scholarships']
        )
        
        print("✅ ML Analytics test completed!")
        print(f"📊 Generated {len(results)} result categories")
        
        # Show sample results
        if 'insights' in results:
            print(f"💡 Generated {len(results['insights'].get('recommendations', []))} recommendations")
            print(f"⚠️  Found {len(results['insights'].get('risk_factors', []))} risk factors")
        
        return True
        
    except Exception as e:
        print(f"❌ ML test failed: {e}")
        return False

def create_directories():
    """Create necessary directories"""
    print("\n📁 Creating ML directories...")
    
    directories = [
        'storage/app/models',
        'storage/app/ml_data',
        'storage/logs/ml'
    ]
    
    for directory in directories:
        os.makedirs(directory, exist_ok=True)
        print(f"✅ Created {directory}")

def main():
    """Main setup function"""
    print("🚀 Setting up ML Analytics for BSU Scholarship System")
    print("=" * 60)
    
    # Create directories
    create_directories()
    
    # Install requirements
    if not install_requirements():
        print("❌ Setup failed at dependency installation")
        return False
    
    # Test ML system
    if not test_ml_system():
        print("❌ Setup failed at ML testing")
        return False
    
    print("\n" + "=" * 60)
    print("🎉 ML Analytics setup completed successfully!")
    print("\n📋 Next steps:")
    print("1. Ensure Python 3.8+ is installed")
    print("2. Run: php artisan config:cache")
    print("3. Test report generation with ML insights")
    print("4. Check storage/app/models/ for trained models")
    
    return True

if __name__ == "__main__":
    success = main()
    sys.exit(0 if success else 1)
