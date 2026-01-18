<script>
let cascadeInitialized = false;

function initializeCascadeDropdowns() {
    console.log('=== Starting initialization ===');
    
    const formationSelect = document.querySelector('select[name="etudiant[id_formation]"]');
    const sectionSelect = document.querySelector('select[name="etudiant[section_id]"]');
    const groupSelect = document.querySelector('select[name="etudiant[group_id]"]');
    
    if (!formationSelect || !sectionSelect || !groupSelect) {
        console.error('One or more select elements not found, retrying...');
        setTimeout(initializeCascadeDropdowns, 200);
        return;
    }
    
    // Get Tom Select instances
    const formationTomSelect = formationSelect.tomselect;
    const sectionTomSelect = sectionSelect.tomselect;
    const groupTomSelect = groupSelect.tomselect;
    
    if (!formationTomSelect || !sectionTomSelect || !groupTomSelect) {
        console.log('Tom Select not initialized yet, retrying...');
        setTimeout(initializeCascadeDropdowns, 200);
        return;
    }
    
    console.log('All Tom Select instances found!');
    
    // Get all sections and groups data from Laravel
    const allSections = @json(
        \App\Models\Section::all()->groupBy('id_formation')->map(function($sections) {
            return $sections->pluck('nom', 'id_section');
        })
    );
    
    const allGroups = @json(
        \App\Models\Groupe::all()->groupBy('id_section')->map(function($groups) {
            return $groups->pluck('nom', 'id_groupe');
        })
    );
    
    console.log('All Sections:', allSections);
    console.log('All Groups:', allGroups);
    
    // Save initial values for editing mode
    const initialSectionId = "{{ $etudiant->section_id ?? '' }}";
    const initialGroupId = "{{ $etudiant->group_id ?? '' }}";
    
    function updateSections(formationId, restoreValue = null) {
        console.log('>>> updateSections called with formationId:', formationId);
        
        // Clear Tom Select options
        sectionTomSelect.clear();
        sectionTomSelect.clearOptions();
        
        // Clear groups too
        groupTomSelect.clear();
        groupTomSelect.clearOptions();
        groupTomSelect.disable();
        
        if (formationId && allSections[formationId]) {
            const sections = allSections[formationId];
            console.log('Found sections for formation', formationId, ':', sections);
            
            // Add each section to Tom Select
            Object.keys(sections).forEach(function(sectionId) {
                sectionTomSelect.addOption({
                    value: sectionId,
                    text: sections[sectionId]
                });
                console.log('Added section option:', sectionId, sections[sectionId]);
            });
            
            sectionTomSelect.enable();
            console.log('Section select enabled with', Object.keys(sections).length, 'options');
            
            // Restore previous value if editing
            if (restoreValue && sections[restoreValue]) {
                sectionTomSelect.setValue(restoreValue);
                console.log('Restored section value to:', restoreValue);
                updateGroups(restoreValue, initialGroupId);
            }
        } else {
            console.log('No sections found for formation:', formationId);
            sectionTomSelect.disable();
        }
    }
    
    function updateGroups(sectionId, restoreValue = null) {
        console.log('>>> updateGroups called with sectionId:', sectionId);
        
        // Clear Tom Select options
        groupTomSelect.clear();
        groupTomSelect.clearOptions();
        
        if (sectionId && allGroups[sectionId]) {
            const groups = allGroups[sectionId];
            console.log('Found groups for section', sectionId, ':', groups);
            
            // Add each group to Tom Select
            Object.keys(groups).forEach(function(groupId) {
                groupTomSelect.addOption({
                    value: groupId,
                    text: groups[groupId]
                });
                console.log('Added group option:', groupId, groups[groupId]);
            });
            
            groupTomSelect.enable();
            console.log('✓ Groups enabled with', Object.keys(groups).length, 'options');
            
            // Restore previous value if editing
            if (restoreValue && groups[restoreValue]) {
                groupTomSelect.setValue(restoreValue);
                console.log('Restored group value to:', restoreValue);
            }
        } else {
            console.log('No groups found for section:', sectionId);
            groupTomSelect.disable();
        }
    }
    
    // Only attach event listeners once
    if (!cascadeInitialized) {
        console.log('Attaching event listeners...');
        
        // Listen for formation changes using Tom Select
        formationTomSelect.on('change', function(value) {
            console.log('!!! Formation changed to:', value);
            updateSections(value);
        });
        
        // Listen for section changes using Tom Select
        sectionTomSelect.on('change', function(value) {
            console.log('!!! Section changed to:', value);
            if (value) {
                updateGroups(value);
            }
        });
        
        cascadeInitialized = true;
        console.log('Event listeners attached');
    }
    
    // Initialize with current formation
    const currentFormation = formationTomSelect.getValue();
    console.log('Current formation value:', currentFormation);
    
    if (currentFormation) {
        console.log('Calling updateSections with formation:', currentFormation);
        updateSections(currentFormation, initialSectionId);
    }
    
    console.log('=== Initialization complete ===');
}

// Wait for DOM and Tom Select to be ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(initializeCascadeDropdowns, 300);
    });
} else {
    setTimeout(initializeCascadeDropdowns, 300);
}

// Also try after a longer delay
setTimeout(initializeCascadeDropdowns, 800);
</script>