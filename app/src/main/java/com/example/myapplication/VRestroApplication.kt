package com.example.myapplication

import android.app.Application
import com.example.myapplication.utils.TokenManager

class VRestroApplication : Application() {
    override fun onCreate() {
        super.onCreate()
        TokenManager.init(applicationContext)
    }
}
