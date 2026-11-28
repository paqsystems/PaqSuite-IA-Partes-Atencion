import 'package:flutter/widgets.dart';

import '../models/partes_user_profile.dart';

class PartesSessionScope extends InheritedWidget {
  const PartesSessionScope({
    super.key,
    required this.profile,
    required super.child,
  });

  final PartesUserProfile? profile;

  static PartesUserProfile? of(BuildContext context) {
    return context
        .dependOnInheritedWidgetOfExactType<PartesSessionScope>()
        ?.profile;
  }

  @override
  bool updateShouldNotify(PartesSessionScope oldWidget) =>
      oldWidget.profile?.tipoFuncional != profile?.tipoFuncional;
}