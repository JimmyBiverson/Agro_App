import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../../core/theme/app_colors.dart';
import '../../../services/api/api_service.dart';
import '../../../providers/auth_provider.dart';

class ChatScreen extends StatefulWidget {
  const ChatScreen({super.key});

  @override
  State<ChatScreen> createState() => _ChatScreenState();
}

class _ChatScreenState extends State<ChatScreen> {
  final TextEditingController _messageController = TextEditingController();
  final ScrollController _scrollController = ScrollController();

  List<Map<String, dynamic>> _conversations = [];
  bool _loadingConversations = false;
  String? _conversationsError;

  Map<String, dynamic>? _selectedConversation;
  List<ChatMessage> _messages = [];
  bool _loadingMessages = false;
  String? _messagesError;
  bool _isSending = false;
  Timer? _pollingTimer;

  @override
  void initState() {
    super.initState();
    _fetchConversations();
  }

  @override
  void dispose() {
    _pollingTimer?.cancel();
    _messageController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _fetchConversations() async {
    if (!mounted) return;
    setState(() {
      _loadingConversations = true;
      _conversationsError = null;
    });

    try {
      final apiService = Provider.of<ApiService>(context, listen: false);
      final conversations = await apiService.getConversations();
      if (!mounted) return;
      setState(() {
        _conversations = conversations;
        _loadingConversations = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _conversationsError = 'Failed to load support tickets. Tap to retry.';
        _loadingConversations = false;
      });
    }
  }

  Future<void> _selectConversation(Map<String, dynamic> conversation) async {
    setState(() {
      _selectedConversation = conversation;
      _messages = [];
      _loadingMessages = true;
      _messagesError = null;
    });

    _pollingTimer?.cancel();
    await _fetchMessages();
    _scrollToBottom(animated: false);

    // Poll for new messages every 5 seconds
    _pollingTimer = Timer.periodic(
      const Duration(seconds: 5),
      (_) => _fetchMessages(silent: true),
    );
  }

  Future<void> _fetchMessages({bool silent = false}) async {
    if (_selectedConversation == null) return;
    final convId = _selectedConversation!['id'].toString();

    if (!silent && !mounted) return;
    if (!silent) {
      setState(() {
        _loadingMessages = true;
        _messagesError = null;
      });
    }

    try {
      final apiService = Provider.of<ApiService>(context, listen: false);
      final currentUserId = Provider.of<AuthProvider>(
        context,
        listen: false,
      ).user?.id;
      final fullConv = await apiService.getConversation(convId);

      if (!mounted || _selectedConversation == null) return;

      final messagesList = (fullConv['messages'] as List? ?? []);
      final newMessages = messagesList.map((m) {
        return ChatMessage(
          id: m['id']?.toString() ?? '',
          text: m['message'] ?? '',
          isSentByUser: m['sender_id']?.toString() == currentUserId?.toString(),
          timestamp: m['created_at'] != null
              ? DateTime.tryParse(m['created_at']) ?? DateTime.now()
              : DateTime.now(),
        );
      }).toList();

      setState(() {
        _messages = newMessages;
        _loadingMessages = false;
      });
      if (!silent) {
        _scrollToBottom();
      }
    } catch (e) {
      if (silent || !mounted) return;
      setState(() {
        _messagesError = 'Failed to load message history.';
        _loadingMessages = false;
      });
    }
  }

  void _closeConversationDetail() {
    _pollingTimer?.cancel();
    setState(() {
      _selectedConversation = null;
      _messages = [];
    });
    _fetchConversations();
  }

  @override
  Widget build(BuildContext context) {
    if (_selectedConversation != null) {
      return _buildChatDetailScreen();
    }
    return _buildConversationListScreen();
  }

  // ══════════════════════════════════════════════════════════════
  // LIST VIEW SCREEN
  // ══════════════════════════════════════════════════════════════

  Widget _buildConversationListScreen() {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Support Tickets'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _fetchConversations,
          ),
        ],
      ),
      body: _loadingConversations && _conversations.isEmpty
          ? const Center(child: CircularProgressIndicator())
          : _conversationsError != null
          ? Center(
              child: InkWell(
                onTap: _fetchConversations,
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Text(
                    _conversationsError!,
                    style: const TextStyle(color: AppColors.error),
                  ),
                ),
              ),
            )
          : _conversations.isEmpty
          ? _buildEmptyState()
          : RefreshIndicator(
              onRefresh: _fetchConversations,
              child: ListView.builder(
                padding: const EdgeInsets.all(12),
                itemCount: _conversations.length,
                itemBuilder: (context, index) {
                  final conv = _conversations[index];
                  return Card(
                    margin: const EdgeInsets.only(bottom: 8),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: ListTile(
                      contentPadding: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 8,
                      ),
                      title: Row(
                        children: [
                          Expanded(
                            child: Text(
                              conv['subject'] ?? 'No Subject',
                              style: const TextStyle(
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                          _buildPriorityBadge(conv['priority']),
                        ],
                      ),
                      subtitle: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const SizedBox(height: 6),
                          Text(
                            conv['latest_message']?['message'] ??
                                'No messages yet',
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(
                              color: AppColors.textSecondary,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                _formatDateTime(conv['created_at']),
                                style: const TextStyle(
                                  fontSize: 11,
                                  color: AppColors.textLight,
                                ),
                              ),
                              _buildStatusBadge(conv['status']),
                            ],
                          ),
                        ],
                      ),
                      trailing: const Icon(
                        Icons.chevron_right,
                        color: AppColors.textLight,
                      ),
                      onTap: () => _selectConversation(conv),
                    ),
                  );
                },
              ),
            ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _showCreateTicketDialog,
        backgroundColor: AppColors.primaryGreen,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add),
        label: const Text('New Ticket'),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(
            Icons.headset_mic_outlined,
            size: 64,
            color: AppColors.textLight,
          ),
          const SizedBox(height: 16),
          const Text(
            'Need assistance?',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: AppColors.textPrimary,
            ),
          ),
          const SizedBox(height: 8),
          const Text(
            'Create a support ticket to start chatting\nwith a member of our team.',
            textAlign: TextAlign.center,
            style: TextStyle(color: AppColors.textSecondary),
          ),
          const SizedBox(height: 24),
          ElevatedButton.icon(
            onPressed: _showCreateTicketDialog,
            icon: const Icon(Icons.add, color: Colors.white),
            label: const Text(
              'Create Support Ticket',
              style: TextStyle(color: Colors.white),
            ),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primaryGreen,
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
            ),
          ),
        ],
      ),
    );
  }

  // ══════════════════════════════════════════════════════════════
  // CHAT DETAIL SCREEN
  // ══════════════════════════════════════════════════════════════

  Widget _buildChatDetailScreen() {
    final status = _selectedConversation!['status'] ?? 'open';
    final isClosed = status == 'closed';
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: _closeConversationDetail,
        ),
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              _selectedConversation!['subject'] ?? 'Support Chat',
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
            Text(
              isClosed ? 'Closed' : 'Online',
              style: theme.textTheme.bodySmall?.copyWith(
                color: isClosed ? AppColors.error : AppColors.success,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
        actions: [
          Container(
            margin: const EdgeInsets.only(right: 16),
            alignment: Alignment.center,
            child: _buildPriorityBadge(_selectedConversation!['priority']),
          ),
        ],
      ),
      body: Column(
        children: [
          if (_messagesError != null)
            Container(
              color: AppColors.error.withValues(alpha: 0.1),
              padding: const EdgeInsets.all(8),
              width: double.infinity,
              child: Text(
                _messagesError!,
                style: const TextStyle(color: AppColors.error, fontSize: 13),
                textAlign: TextAlign.center,
              ),
            ),
          Expanded(
            child: _loadingMessages && _messages.isEmpty
                ? const Center(child: CircularProgressIndicator())
                : ListView.builder(
                    controller: _scrollController,
                    padding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 12,
                    ),
                    itemCount: _messages.length,
                    itemBuilder: (context, index) {
                      return _buildMessageBubble(_messages[index]);
                    },
                  ),
          ),
          if (isClosed)
            Container(
              color: AppColors.textLight.withValues(alpha: 0.1),
              padding: const EdgeInsets.all(16),
              alignment: Alignment.center,
              child: const Text(
                'This ticket has been resolved and closed.',
                style: TextStyle(
                  color: AppColors.textSecondary,
                  fontStyle: FontStyle.italic,
                ),
              ),
            )
          else
            _buildMessageInput(),
        ],
      ),
    );
  }

  Widget _buildMessageBubble(ChatMessage message) {
    final isSentByUser = message.isSentByUser;

    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        mainAxisAlignment: isSentByUser
            ? MainAxisAlignment.end
            : MainAxisAlignment.start,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (!isSentByUser) ...[
            const CircleAvatar(
              radius: 16,
              backgroundColor: AppColors.primaryGreen,
              child: Icon(Icons.support_agent, color: Colors.white, size: 18),
            ),
            const SizedBox(width: 8),
          ],
          Flexible(
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              decoration: BoxDecoration(
                color: isSentByUser
                    ? AppColors.primaryGreen
                    : AppColors.backgroundLight,
                borderRadius: BorderRadius.only(
                  topLeft: const Radius.circular(16),
                  topRight: const Radius.circular(16),
                  bottomLeft: Radius.circular(isSentByUser ? 16 : 4),
                  bottomRight: Radius.circular(isSentByUser ? 4 : 16),
                ),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    message.text,
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                      color: isSentByUser
                          ? Colors.white
                          : AppColors.textPrimary,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    _formatTime(message.timestamp),
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: isSentByUser
                          ? Colors.white.withValues(alpha: 0.7)
                          : AppColors.textLight,
                      fontSize: 11,
                    ),
                  ),
                ],
              ),
            ),
          ),
          if (isSentByUser) ...[
            const SizedBox(width: 8),
            CircleAvatar(
              radius: 16,
              backgroundColor: AppColors.primaryGreen.withValues(alpha: 0.2),
              child: const Icon(
                Icons.person,
                color: AppColors.primaryGreen,
                size: 18,
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildMessageInput() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surfaceWhite,
        boxShadow: [
          BoxShadow(
            color: AppColors.textPrimary.withValues(alpha: 0.1),
            blurRadius: 4,
            offset: const Offset(0, -2),
          ),
        ],
      ),
      child: SafeArea(
        child: Row(
          children: [
            Expanded(
              child: TextField(
                controller: _messageController,
                decoration: InputDecoration(
                  hintText: 'Type a message...',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(24),
                    borderSide: BorderSide.none,
                  ),
                  filled: true,
                  fillColor: AppColors.backgroundLight,
                  contentPadding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 12,
                  ),
                ),
                maxLines: null,
                textInputAction: TextInputAction.send,
                onSubmitted: (_) => _sendMessage(),
              ),
            ),
            const SizedBox(width: 8),
            Container(
              decoration: const BoxDecoration(
                color: AppColors.primaryGreen,
                shape: BoxShape.circle,
              ),
              child: _isSending
                  ? const Padding(
                      padding: EdgeInsets.all(12),
                      child: SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: Colors.white,
                        ),
                      ),
                    )
                  : IconButton(
                      icon: const Icon(
                        Icons.send,
                        color: Colors.white,
                        size: 20,
                      ),
                      onPressed: _sendMessage,
                    ),
            ),
          ],
        ),
      ),
    );
  }

  void _sendMessage() async {
    final text = _messageController.text.trim();
    if (text.isEmpty || _isSending || _selectedConversation == null) return;

    setState(() => _isSending = true);
    final convId = _selectedConversation!['id'].toString();

    try {
      final apiService = Provider.of<ApiService>(context, listen: false);
      await apiService.sendMessage(convId, text);
      _messageController.clear();
      if (!mounted) return;
      setState(() => _isSending = false);
      _fetchMessages();
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isSending = false;
        _messagesError = 'Failed to send message. Please try again.';
      });
    }
  }

  void _scrollToBottom({bool animated = true}) {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollController.hasClients) {
        if (animated) {
          _scrollController.animateTo(
            _scrollController.position.maxScrollExtent,
            duration: const Duration(milliseconds: 300),
            curve: Curves.easeOut,
          );
        } else {
          _scrollController.jumpTo(_scrollController.position.maxScrollExtent);
        }
      }
    });
  }

  // ══════════════════════════════════════════════════════════════
  // DIALOGS & FORMATTERS
  // ══════════════════════════════════════════════════════════════

  void _showCreateTicketDialog() {
    final subjectController = TextEditingController();
    final messageController = TextEditingController();
    String currentPriority = 'normal';
    bool dialogSubmitting = false;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              title: const Text('Start Support Ticket'),
              content: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    TextField(
                      controller: subjectController,
                      decoration: const InputDecoration(
                        labelText: 'Subject',
                        hintText: 'e.g. Broken stock delivery',
                      ),
                      enabled: !dialogSubmitting,
                    ),
                    const SizedBox(height: 12),
                    DropdownButtonFormField<String>(
                      initialValue: currentPriority,
                      decoration: const InputDecoration(labelText: 'Priority'),
                      items: const [
                        DropdownMenuItem(value: 'low', child: Text('Low')),
                        DropdownMenuItem(
                          value: 'normal',
                          child: Text('Normal'),
                        ),
                        DropdownMenuItem(value: 'high', child: Text('High')),
                        DropdownMenuItem(
                          value: 'urgent',
                          child: Text('Urgent'),
                        ),
                      ],
                      onChanged: dialogSubmitting
                          ? null
                          : (val) {
                              if (val != null) {
                                setDialogState(() => currentPriority = val);
                              }
                            },
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: messageController,
                      decoration: const InputDecoration(
                        labelText: 'Initial Message',
                        hintText: 'Describe your query here...',
                      ),
                      maxLines: 4,
                      enabled: !dialogSubmitting,
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: dialogSubmitting
                      ? null
                      : () => Navigator.pop(context),
                  child: const Text('Cancel'),
                ),
                ElevatedButton(
                  onPressed: dialogSubmitting
                      ? null
                      : () async {
                          final subject = subjectController.text.trim();
                          final message = messageController.text.trim();
                          if (subject.isEmpty || message.isEmpty) return;

                          setDialogState(() => dialogSubmitting = true);

                          try {
                            final apiService = Provider.of<ApiService>(
                              context,
                              listen: false,
                            );
                            final response = await apiService
                                .createConversation(subject, message);

                            // Pop dialog
                            if (!context.mounted) return;
                            Navigator.pop(context);

                            // Load the newly created ticket
                            _selectConversation(response);
                          } catch (e) {
                            if (!context.mounted) return;
                            setDialogState(() => dialogSubmitting = false);
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(
                                content: Text(
                                  'Failed to start ticket. User role validation error?',
                                ),
                              ),
                            );
                          }
                        },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.primaryGreen,
                  ),
                  child: dialogSubmitting
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        )
                      : const Text(
                          'Submit',
                          style: TextStyle(color: Colors.white),
                        ),
                ),
              ],
            );
          },
        );
      },
    );
  }

  Widget _buildPriorityBadge(String? priority) {
    Color color;
    switch (priority) {
      case 'low':
        color = Colors.grey;
        break;
      case 'high':
        color = Colors.orange;
        break;
      case 'urgent':
        color = Colors.red;
        break;
      case 'normal':
      default:
        color = AppColors.primaryGreen;
        break;
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withValues(alpha: 0.5)),
      ),
      child: Text(
        priority?.toUpperCase() ?? 'NORMAL',
        style: TextStyle(
          color: color,
          fontSize: 10,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }

  Widget _buildStatusBadge(String? status) {
    final closed = status == 'closed';
    final color = closed ? Colors.red : AppColors.success;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(4),
      ),
      child: Text(
        closed ? 'Closed' : 'Open',
        style: TextStyle(
          color: color,
          fontSize: 11,
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }

  String _formatDateTime(String? dateStr) {
    if (dateStr == null) return '';
    final parsed = DateTime.tryParse(dateStr);
    if (parsed == null) return dateStr;
    final year = parsed.year;
    final month = parsed.month.toString().padLeft(2, '0');
    final day = parsed.day.toString().padLeft(2, '0');
    final hour = parsed.hour.toString().padLeft(2, '0');
    final minute = parsed.minute.toString().padLeft(2, '0');
    return '$year-$month-$day $hour:$minute';
  }

  String _formatTime(DateTime dateTime) {
    final hour = dateTime.hour.toString().padLeft(2, '0');
    final minute = dateTime.minute.toString().padLeft(2, '0');
    return '$hour:$minute';
  }
}

class ChatMessage {
  final String id;
  final String text;
  final bool isSentByUser;
  final DateTime timestamp;

  const ChatMessage({
    required this.id,
    required this.text,
    required this.isSentByUser,
    required this.timestamp,
  });
}
