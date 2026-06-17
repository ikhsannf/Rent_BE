import 'package:flutter/material.dart';
import 'main_layout.dart'; // Import file baru tadi

void main() {
  runApp(const RentStuffApp());
}

class RentStuffApp extends StatelessWidget {
  const RentStuffApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'RentStuff',
      theme: ThemeData(
        primarySwatch: Colors.blue,
      ),
      home: const MainLayout(),
    );
  }
}