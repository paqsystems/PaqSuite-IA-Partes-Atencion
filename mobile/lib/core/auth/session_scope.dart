import 'package:flutter/widgets.dart';

import 'auth_service.dart';

class SessionScope extends InheritedWidget {
  const SessionScope({
    super.key,
    required this.session,
    required super.child,
  });

  final AuthSession? session;

  static AuthSession? of(BuildContext context) {
    return context
        .dependOnInheritedWidgetOfExactType<SessionScope>()
        ?.session;
  }

  @override
  bool updateShouldNotify(SessionScope oldWidget) =>
      oldWidget.session?.accessToken != session?.accessToken ||
      oldWidget.session?.tenantCode != session?.tenantCode;
}