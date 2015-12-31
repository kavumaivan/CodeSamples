
'######################################
' Author Ivan Kavuma 12/15/2006
' Washington State Bar.
'#########################################

Class ClsTree_TM
      Option Explicit
    Public ParentID As Integer
    Public id As Integer
    Public name As String
    Public tree As Collection

    Private Sub Class_Initialize()
        tree = New Collection
    End Sub

End Class

Class rule_manager Form
Option Explicit
    Private NUMRULES As Integer
    Private strRule_Table(200, 3) As String
    Private strColUsed As Collection
    Private myRules As ClsTree_RM
    ' Purpose:  Returns True if the node found and False if not found
    ' Modified: IK 12-14-2006  Created
    Private Function FindUsed(ByVal str As String) As Boolean
        'Error handler
        On Error GoTo EH

        Dim j As Integer, i As Integer
        i = strColUsed.Count
        j = 1
        Do While j < i

            If strColUsed.Item(j) = str Then

                FindUsed = True

                Exit Function

            End If
            j = j + 1
        Loop
        FindUsed = False

        Exit Function    'Normal exit point
EH:     'Error handling
        'Display error
        Call MsgBox(Err.Description)
    End Function
    ' Purpose:  To search on the LEFT side of the array (strRule_Table(i, 1))
    '           for rule other than the one past into the function.
    '           return another of index this rule that is not already used.
    ' Modified: IK 12-14-2006  Created
    Private Function Search_RM(ByVal rule As String, ByVal RuleIndex As Integer) As Integer
        'Error handler
        On Error GoTo EH
        Dim i As Integer


        For i = 0 To NUMRULES
            If strRule_Table(i, 1) = rule And RuleIndex <> i Then
                'Return the first index found that is not used.
                If FindUsed(strRule_Table(i, 0)) = False Then

                    Search_RM = i
                    Exit Function

                End If
            End If

        Next i
        Search_RM = -1

        Exit Function    'Normal exit point
EH:     'Error handling
        'Display error
        Call MsgBox(Err.Description)
    End Function
    ' Purpose:  Adds a rule_ID that has been used if its not already added.
    ' Modified: IK 12-14-2006  Created
    Private Sub AddUsed(ByVal str As String)
        'Error handler
        On Error GoTo EH
        If FindUsed(str) = False Then
            Call strColUsed.Add(str)
        End If
        Exit Sub    'Normal exit point
EH:     'Error handling
        'Display error
        Call MsgBox(Err.Description)
    End Sub

    ' Purpose:  Searches for a name of the node in a given tree and returns a reference to the tree if found
    '           Otherwise returns nothing if not found.
    ' Modified: IK 12-14-2006  Created
    Private Function searchTree(ByVal name As String, ByVal TheTree As ClsTree_RM) As ClsTree_RM
        'Error handler
        On Error GoTo EH
        Dim childtree As Object

        If TheTree.name = name Then
            searchTree = TheTree
            Exit Function
        Else
            For Each childtree In TheTree.tree

                Dim chTree As ClsTree_RM
                chTree = New ClsTree_RM
                chTree = childtree
                chTree = searchTree(name, chTree)

                If Not chTree Is Nothing Then
                    searchTree = chTree
                    Exit Function
                Else
                    chTree = Nothing
                End If

            Next
            searchTree = Nothing
        End If

        Exit Function    'Normal exit point
EH:     'Error handling
        'Display error
        Call MsgBox(Err.Description)

    End Function

    ' Purpose:  This function is the meat of the entire tree structure.  It is recrusive and builds the entire tree structure.
    '           1   Each node in the tree has a name and may have a collection of other nodes with similar structure.
    '               (Tree, node and rule are used interchangeably)
    '           2   When building the tree we start at the top of the two dimentional array (strRule_Table(200,3))
    '               which has a Left side (Occurring rule) and Right side (resulting rule) as show in the rule manager.
    '                The left side isstrRule_Table(i, 1), Right side is strRule_Table(i, 2)
    '           3    i)  start each call to this function by creatng a node (myRuleTree) and all its details.
    '                ii) We look for the RIGHT node (RightIndex = Search_RM(strRule_Table(ruleIndex, 2), ruleIndex)) on
    '                    the LEFT side of the array(strRule_Table)
    '               iii) We log every call to search with the rule_ID in to a collection (strColUsed)
    '                    via a sub (Call AddUsed(strRule_Table(ruleIndex, 0)))
    '                a When right node is NOT found we add the right side of the ruleIndex (strRule_Table(ruleIndex, 2))
    '                    as the lastnode to left node(LastNode) added earlier.

    '                b  When found, the ruleindex is pasted again to this function in a recursive call to self.

    '           4    i)  We search for the LEFT node on the LEFT side of the
    '                    array (LeftIndex = Search_RM(strRule_Table(ruleIndex, 1), ruleIndex)) as long as its found.
    '                ii) When found we continue to search for the left side in the tree built thus far
    '                          a) if right node is found we add it to the myRuleTree being built in this call
    '                              and then look to see if it has a child.
    '                              if it has a child its added to this right node by calling this function again recursively
    '                              else just log the rule Id.
    '                               continue searching all the left node until no more is found. at this level.
    '                           b) if right node is not found we exit
    '
    '             5   return the built node (myRuleTree) thus far to the original caller.
    ' Modified: IK 12-14-2006  Created
    Private Function Build_RM(ByVal RuleIndex As Integer) As ClsTree_RM
        'Error handler
        On Error GoTo EH
        Dim LeftIndex As Integer, RightIndex As Integer
        Dim myRuleTree As ClsTree_RM
        myRuleTree = New ClsTree_RM
        myRuleTree.name = strRule_Table(RuleIndex, 0) + " " + strRule_Table(RuleIndex, 1)

        Call AddUsed(strRule_Table(RuleIndex, 0))

        RightIndex = Search_RM(strRule_Table(RuleIndex, 2), RuleIndex)


        If RightIndex = -1 Then

            Call AddUsed(strRule_Table(RuleIndex, 0))
            Dim LastNode As ClsTree_RM
            LastNode = New ClsTree_RM
            LastNode.name = strRule_Table(RuleIndex, 0) + " " + strRule_Table(RuleIndex, 2)
            Call myRuleTree.tree.Add(LastNode)
        Else

            Call AddUsed(strRule_Table(RightIndex, 0))
            Call myRuleTree.tree.Add(Build_RM(RightIndex))
        End If

        Do   'Add all the nodes at this point.

            'search for the left node on the left side of the array.
            LeftIndex = Search_RM(strRule_Table(RuleIndex, 1), RuleIndex)

            Call AddUsed(strRule_Table(RuleIndex, 0))

            'If found add all its details.
            If LeftIndex <> -1 Then

                Dim LeftTree As ClsTree_RM
                LeftTree = New ClsTree_RM
                LeftTree.name = strRule_Table(LeftIndex, 0) + " " + strRule_Table(LeftIndex, 2)
                Call AddUsed(strRule_Table(LeftIndex, 0))
                Call myRuleTree.tree.Add(LeftTree)

                'Check for a left node for more tree.
                Dim insideRight As Integer
                insideRight = Search_RM(strRule_Table(LeftIndex, 2), LeftIndex)

                'add the left node child tree to this left tree.
                If insideRight <> -1 Then
                    Call AddUsed(strRule_Table(insideRight, 0))
                    Call LeftTree.tree.Add(Build_RM(insideRight))
                End If

            End If


            'look for the next index on the left.
            RuleIndex = LeftIndex
        Loop Until LeftIndex = -1    'Continue until all the left nodes are added at this level.
        Build_RM = myRuleTree
        Exit Function    'Normal exit point
EH:     'Error handling
        'Display error
        Call MsgBox(Err.Description)

    End Function


    Private Sub Driver_RM()
        'Error handler
        On Error GoTo EH
        Dim i As Integer


        For i = 0 To NUMRULES

            If FindUsed(strRule_Table(i, 0)) = False Then

                Call myRules.tree.Add(Build_RM(i))
            End If

        Next i

        Exit Sub    'Normal exit point
EH:     'Error handling
        'Display error
        Call MsgBox(Err.Description)
    End Sub
    'Not used any more!
    'Private Sub printTree(MainTree As ClsTree_RM)
    '    Dim childtree As Variant
    '
    '
    '        txtRuleManager.Text = txtRuleManager.Text & Chr(13) & Chr(10) & _
    '                            "Parentid: " & MainTree.ParentID & _
    '                            "  mainTree.id" & MainTree.ID & "  " & MainTree.name
    '
    '
    '
    '        For Each childtree In MainTree.tree
    '                Dim chTree As ClsTree_RM
    '                Set chTree = New ClsTree_RM
    '                Set chTree = childtree
    '                Call printTree(chTree)
    '        Next
    '
    'End Sub
    ' Purpose:  This sub is responsible for populating the array strRule_Table with data.
    ' Modified: 12-14-2006  IK Created
    Private Sub Fill_Type_Grid()
        'Error handler
        On Error GoTo EH

        Dim rsData As ADODB.Recordset
        Dim objUtils As clsUtils
        Dim i As Integer

        objUtils = Nothing

        objUtils = New clsUtils
        rsData = New ADODB.Recordset
        objUtils.strDatabase = strDatabase

        rsData = objUtils.mthRead(52)

        NUMRULES = rsData.RecordCount

        rsData.MoveFirst()

        Do While Not rsData.EOF

            If Not IsNull(rsData.Fields(0)) And _
                Not IsNull(rsData.Fields(1)) And _
                Not IsNull(rsData.Fields(2)) And _
                Not IsNull(rsData.Fields(3)) And _
                Not IsNull(rsData.Fields(4)) Then

                strRule_Table(i, 0) = rsData.Fields(0)
                strRule_Table(i, 1) = rsData.Fields(1) + " " + rsData.Fields(2)
                strRule_Table(i, 2) = rsData.Fields(3) + " " + rsData.Fields(4)

            End If

            rsData.MoveNext()
            i = i + 1

        Loop

        ' Clean up
        objUtils = Nothing

        Exit Sub
EH:
        If blnDEBUG Then
            Call MsgBox("Discipline System Error: Error while filling grid." & Chr(10) & "" & Chr(10) & Chr(10) & "System: DisciplineApp:frmDSUtils:Fill_Type_Grid" & Chr(10) & "Description: " & Err.Description, vbCritical, "Discipline System Error")
        Else
            Call MsgBox("Discipline System Error: Error while filling grid." & Err.Description, vbCritical, "Discipline System Error")
        End If
    End Sub

    ' Purpose:  This sub controls the entire rule manager.
    ' Modified: 12-14-2006  IK Created
    Private Sub Form_Load()
        'Error handler
        On Error GoTo EH
        strColUsed = New Collection
        myRules = New ClsTree_RM
        myRules.ID = 0
        myRules.name = "Root"
        Call Fill_Type_Grid()

        Call InitTreeView()

        Call Driver_RM()

        'Call printTree(myRules)

        Call AddTreeView(myRules, 0)
        Exit Sub    'Normal exit point
EH:     'Error handling
        'Display error
        Call MsgBox(Err.Description)
    End Sub
    Private Sub Form_Resize()
        'Error handler
        On Error GoTo EH

        trvRuleManager.Height = Me.Height - 1000
        trvRuleManager.Width = Me.Width - 500

        Exit Sub    'Normal exit point
EH:     'Error handling
        'Display error
        Call MsgBox(Err.Description)
    End Sub

    ' Purpose:  This sub initiates the tree view.
    ' Modified: 12-14-2006  IK Created
    Private Sub InitTreeView()
        'Error handler
        On Error GoTo EH
        ' Load a bitmap into an Imagelist control.
        Dim imgX As ListImage
        Dim BitmapPath As String
        BitmapPath = "C:\DISCSYS\Development\Graphics\List.ico" ' Change to a valid path.
        imgX = ImageList1.ListImages.Add(, , LoadPicture(BitmapPath))

        ' Initialize TreeView control and create several nodes.
        trvRuleManager.ImageList = ImageList1

        Exit Sub    'Normal exit point
EH:     'Error handling
        'Display error
        Call MsgBox(Err.Description)
    End Sub

    ' Purpose:  This sub adds the myRules tree to the treeview control.
    ' Modified: 12-14-2006  IK Created
    Private Sub AddTreeView(ByVal TheTree As ClsTree_RM, ByVal ParentIndex As Integer)
        'Error handler
        On Error GoTo EH
        Dim nodX As Node   ' Create a tree.
        Dim childtree As Object
        If ParentIndex = 0 Then
            'to add the root tree.
            nodX = trvRuleManager.Nodes.Add(, , , TheTree.name, , 1)
        Else
            'All other trees below root.
            nodX = trvRuleManager.Nodes.Add(ParentIndex, tvwChild, , TheTree.name, , 1)
        End If


        'for each child pass the parent index to create a relation ship between the parent and the child.
        For Each childtree In TheTree.tree

            Dim chTree As ClsTree_RM
            chTree = New ClsTree_RM
            chTree = childtree

            Call AddTreeView(chTree, nodX.Index)

        Next

        nodX.EnsureVisible() ' Expand tree to show all nodes.

        Exit Sub    'Normal exit point
EH:     'Error handling
        'Display error
        Call MsgBox(Err.Description)

    End Sub
    Private Sub Form_Terminate()
        'Error handler
        On Error GoTo EH
        strColUsed = Nothing
        myRules = Nothing

EH:     'Error handling
        'Display error
        Call MsgBox(Err.Description)


    End Sub

End Class
